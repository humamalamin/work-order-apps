<?php

namespace App\Filament\Exports;

use App\Models\OperatorReport;
use App\Models\WorkOrder;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class OperatorReportExporter extends Exporter
{
    public static function modifyQuery(Builder $query): Builder
    {
        return WorkOrder::query()
            ->join('users', 'work_orders.assigned_operator_id', '=', 'users.id')
            ->selectRaw("work_orders.id, users.name as operator_name, 
        work_orders.product_name,
        SUM(CASE WHEN work_orders.status = 'Completed' THEN work_orders.quantity ELSE 0 END) as completed_quantity")
            ->groupBy('users.name', 'work_orders.product_name', 'work_orders.id');
    }
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('operator_name')
                ->label(__('Operator Name')),
            ExportColumn::make('product_name')
                ->label(__('Product Name')),
            ExportColumn::make('completed_quantity')
                ->label(__('Completed Quantity')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your operator report export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
