<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers\ProductionLogsRelationManager;
use App\Models\ProductionLog;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\AssignedOperatorNotification;
use App\Notifications\UpdateStatusOperatorNotification;
use App\Notifications\UpdateStatusPmNotification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('work_order_number')
                    ->label(__('Work Order Number'))
                    ->disabled(),
                TextInput::make('product_name')
                    ->label(__('Product Name'))
                    ->required(),
                TextInput::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->required(),
                DateTimePicker::make('deadline')
                    ->label(__('Deadline'))
                    ->required(),
                Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'Pending' => 'Pending',
                        'In Progress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Canceled' => 'Canceled',
                    ])
                    ->required(),
                Select::make('assigned_operator_id')
                    ->label(__('Assigned Operator'))
                    ->relationship('assignedOperator', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('work_order_number')
                    ->label(__('Work Order Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_name')
                    ->label(__('Product Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedOperator.name')
                    ->label(__('Assigned Operator'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'gray',
                        'In Progress' => 'info',
                        'Completed' => 'success',
                        'Canceled' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->multiple()
                    ->options([
                        'Pending' => 'Pending',
                        'In Progress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Canceled' => 'Canceled',
                    ])
                    ->attribute('status'),
                SelectFilter::make('assignedOperator')
                    ->relationship('assignedOperator', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('deadline')
                    ->form([
                        DatePicker::make('deadline_start')
                            ->label(__('Start Date')),
                        DatePicker::make('deadline_end')
                            ->label(__('End Date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['deadline_start'],
                                fn (Builder $query, $date): Builder => $query->whereDate('deadline', '>=', $date),
                            )
                            ->when(
                                $data['deadline_end'],
                                fn (Builder $query, $date): Builder => $query->whereDate('deadline', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('updateStatus')
                        ->label(__('Update Status'))
                        ->color('info')
                        ->icon('heroicon-o-arrow-path')
                        ->visible(fn (WorkOrder $record) => $record->status != 'Completed')
                        ->form(function () {
                            $statusOptions = [
                                'In Progress' => 'In Progress',
                                'Completed' => 'Completed',
                            ];

                            if (Auth::user()->can('cancel work order')) {
                                $statusOptions['Canceled'] = 'Canceled';
                            }

                            return [
                                Select::make('status')
                                    ->label('Status Baru')
                                    ->options($statusOptions)
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required(),
                                Textarea::make('status_notes')
                                    ->label(__('Status Notes')),
                            ];
                        })
                        ->visible(fn (): bool => Auth::user()->can('update status'))
                        ->action(function (array $data, WorkOrder $record) {
                            if ($data['status'] === 'Canceled' && !Auth::user()->can('cancel work order')) {
                                throw new \Exception('Hanya Production Manager yang bisa membatalkan work order.');
                            }

                            if ($record->status === 'Pending' && $data['status'] !== 'In Progress' && Auth::user()->hasRole('operator')) {
                                throw new \Exception('Status hanya bisa diubah ke In Progress.');
                            }

                            if ($record->status === 'In Progress' && !in_array($data['status'], ['Completed', 'Canceled']) && Auth::user()->hasRole('operator')) {
                                throw new \Exception('Status hanya bisa diubah ke Completed atau Canceled.');
                            }

                            $now = Carbon::now();
                            $logData = [
                                'status_notes' => 'Status diubah ke ' . $data['status'],
                                'quantity' => $data['quantity'],
                            ];

                            if ($data['status'] === 'In Progress') {
                                $logData['start_time'] = $now;
                            }

                            if (in_array($data['status'], ['Completed', 'Canceled'])) {
                                $logData['end_time'] = $now;

                                $lastLog = $record->productionLogs()
                                    ->whereNotNull('start_time')
                                    ->latest()
                                    ->first();

                                if ($lastLog) {
                                    $startTime = Carbon::parse($lastLog->start_time);
                                    $endTime = $now;
                                    $logData['duration'] = $endTime->diffInMinutes($startTime);
                                }
                            }

                            $record->update(['status' => $data['status']]);

                            $record->productionLogs()->create($logData);

                            $pm = Auth::user()->hasRole('project manager') ? Auth::user() : User::find($record->created_by_id);
                            FacadesNotification::send($pm, new UpdateStatusPmNotification($record));

                            $operator = Auth::user()->hasRole('operator') ? Auth::user() : User::find($record->assigned_operator_id);
                            FacadesNotification::send($operator, new UpdateStatusOperatorNotification($record));

                            Notification::make()
                                ->title('Status Work Order Diupdate')
                                ->body('Status work order ' . $record->work_order_number . ' berhasil diubah ke ' . $data['status'])
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([
                                Tables\Actions\BulkActionGroup::make([
                                    Tables\Actions\DeleteBulkAction::make(),
                                ]),
                            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductionLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (Auth::user()->hasRole('operator')) {
            return parent::getEloquentQuery()->where('assigned_operator_id', Auth::user()->id);
        }

        return parent::getEloquentQuery();
    }
}
