<?php

namespace App\Filament\Widgets;

use App\Filament\Exports\OperatorReportExporter;
use App\Models\WorkOrder;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OperatorReport extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    public function getTableQuery(): Builder
    {
        $query = WorkOrder::query();
        if (Auth::user()->isOperator()) {
            $query = $query->where("assigned_operator_id", Auth::user()->id);
        }
        return $query
                ->join('users', 'work_orders.assigned_operator_id', '=', 'users.id')
                ->selectRaw("work_orders.id, users.name as operator_name, 
                    work_orders.product_name,
                    SUM(CASE WHEN work_orders.status = 'Completed' THEN work_orders.quantity ELSE 0 END) as completed_quantity")
                ->groupBy('users.name', 'work_orders.product_name', 'work_orders.id');
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('operator_name')
                ->label(__('Operator Name')),
            TextColumn::make('product_name')
                ->label(__('Product Name')),
            TextColumn::make('completed_quantity')
                ->label(__('Completed Quantity')),
        ];
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->worker_id;
    }

    public function getTableHeaderActions(): array
    {
        return [
            ExportAction::make('export')
                ->label('Ekspor ke Excel')
                ->exporter(OperatorReportExporter::class),
        ];
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }


}
