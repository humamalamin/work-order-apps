<?php

namespace App\Filament\Exports;

use App\Models\WorkOrder;
use App\Models\WorkOrderReport;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderReportExporter extends Exporter
{
    public static function modifyQuery(Builder $query): Builder
    {
        return WorkOrder::query()
            ->selectRaw("product_name,work_orders.id,
                SUM(CASE WHEN status = 'Pending' THEN quantity ELSE 0 END) as pending_quantity,
                SUM(CASE WHEN status = 'In Progress' THEN quantity ELSE 0 END) as in_progress_quantity,
                SUM(CASE WHEN status = 'Completed' THEN quantity ELSE 0 END) as completed_quantity,
                SUM(CASE WHEN status = 'Canceled' THEN quantity ELSE 0 END) as canceled_quantity")
            ->groupBy('product_name', 'work_orders.id');
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product_name')
                ->label(__('Product Name')),
            ExportColumn::make('pending_quantity')
                ->label(__('Pending')),
            ExportColumn::make('in_progress_quantity')
                ->label(__('In Progress')),
            ExportColumn::make('completed_quantity')
                ->label(__('Completed')),
            ExportColumn::make('canceled_quantity')
                ->label(__('Canceled')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your work order report export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
