<?php

namespace App\Providers\Filament;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\WorkOrderResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Dashboard as PagesDashboard;
use App\Filament\Widgets\OperatorReport;
use App\Filament\Widgets\WorkOrderReport;
use Filament\Pages;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseTransactions()
            ->sidebarCollapsibleOnDesktop(true)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                OperatorReport::class,
                WorkOrderReport::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->groups([
                    NavigationGroup::make()
                        ->items([
                            NavigationItem::make('dashboard')
                                ->label(fn (): string => __('filament-panels::pages/dashboard.title'))
                                ->url(fn (): string => PagesDashboard::getUrl())
                                ->icon('heroicon-o-building-storefront')
                                ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.dashboard')),
                        ]),
                    NavigationGroup::make('Management')
                        ->items([
                            NavigationItem::make('Work Order')
                                ->icon('heroicon-o-rectangle-stack')
                                ->url(url: fn (): string => WorkOrderResource::getUrl())
                                ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.work-orders.index')),
                            NavigationItem::make('User')
                                ->icon('heroicon-o-users')
                                ->url(url: fn (): string => UserResource::getUrl())
                                ->visible(fn (): bool => Auth::user()->isSuperAdmin() || Auth::user()->isPm())
                                ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.users.index')),
                        ]),
                    NavigationGroup::make(__('Settings'))
                        ->items([
                            NavigationItem::make(__('Role'))
                                ->icon('heroicon-o-lock-closed')
                                ->url(fn (): string => RoleResource::getUrl())
                                ->isActiveWhen(fn () => request()->routeIs('filament.admin.resources.roles.index'))
                                ->visible(fn (): bool => Auth::user()->isSuperAdmin()),
                        ]),
                ]);

            });
    }
}
