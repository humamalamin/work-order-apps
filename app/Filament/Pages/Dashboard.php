<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OperatorReport;
use App\Filament\Widgets\OperatorReportCart;
use App\Filament\Widgets\WorkOrderReport;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        $widgets = [
            OperatorReport::class,
        ];

        if (!Auth::user()->isOperator()) {
            $widgets[] = WorkOrderReport::class;
        }
        return $widgets;
    }

    public function getColumns(): int
    {
        $column = 1;
        if (!Auth::user()->isOperator()) {
            $column = 2;
        }
        return $column;
    }
}
