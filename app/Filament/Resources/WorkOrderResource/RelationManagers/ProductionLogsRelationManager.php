<?php

namespace App\Filament\Resources\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class ProductionLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'productionLogs';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status_notes')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('start_time')
                    ->required(),
                DateTimePicker::make('end_time')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status_notes')
            ->columns([
                Tables\Columns\TextColumn::make('status_notes')
                    ->label(__('Status Notes')),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('Quantity')),
                Tables\Columns\TextColumn::make('start_time')
                    ->label(__('Start Time'))
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/M/Y H:i:s') : '-'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label(__('End Time'))
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d/M/Y H:i:s') : '-'),
                Tables\Columns\TextColumn::make('duration')
                    ->label(__('Duration'))
                    ->formatStateUsing(fn ($state) => abs(ceil($state)). ' ' .__('Minutes'))
            ])
            ->filters([
                Filter::make('duration')
                    ->form([
                        DatePicker::make('start_time')
                            ->label(__('Start Time')),
                        DatePicker::make('end_time')
                            ->label(__('End Time')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_time'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['end_time'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_time', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ]);
    }
}
