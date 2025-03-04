<?php

namespace App\Filament\Widgets;

use App\Filament\Exports\WorkOrderReportExporter;
use App\Models\WorkOrder;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderReport extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    public function getTableQuery(): Builder
    {
        return WorkOrder::query()
            ->selectRaw("product_name,work_orders.id,
                SUM(CASE WHEN status = 'Pending' THEN quantity ELSE 0 END) as pending_quantity,
                SUM(CASE WHEN status = 'In Progress' THEN quantity ELSE 0 END) as in_progress_quantity,
                SUM(CASE WHEN status = 'Completed' THEN quantity ELSE 0 END) as completed_quantity,
                SUM(CASE WHEN status = 'Canceled' THEN quantity ELSE 0 END) as canceled_quantity")
            ->groupBy('product_name', 'work_orders.id');
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('product_name')
                ->label(__('Product Name')),
            TextColumn::make('pending_quantity')
                ->label(__('Pending'))
                ->numeric(),
            TextColumn::make('in_progress_quantity')
                ->label(__('In Progress'))
                ->numeric(),
            TextColumn::make('completed_quantity')
                ->label(__('Completed'))
                ->numeric(),
            TextColumn::make('canceled_quantity')
                ->label(__('Canceled'))
                ->numeric(),
        ];
    }

    public function getTableHeaderActions(): array
    {
        return [
            ExportAction::make('export')
                ->label('Ekspor ke Excel')
                ->exporter(WorkOrderReportExporter::class),
        ];
    }
}
