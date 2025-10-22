<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login() // Use default Filament login
            ->authGuard('web')
            ->emailVerification(false)
            ->requiresEmailVerification(false) // Try this additional config
            
            // PERFORMANCE OPTIMIZATIONS
            ->databaseNotifications() // Use database for notifications (faster than polling)
            ->databaseNotificationsPolling('30s') // Poll every 30 seconds instead of default
            ->spa() // Single Page Application mode - faster navigation
            
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                \App\Filament\Resources\CompanyResource::class,
                \App\Filament\Resources\UserResource::class,
                \App\Filament\Resources\CustomerResource::class,
                \App\Filament\Resources\SupplierResource::class,
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\PriceQuotationResource::class,
                \App\Filament\Resources\PurchaseOrderResource::class,
                \App\Filament\Resources\DeliveryNoteResource::class,
                \App\Filament\Resources\InvoiceResource::class,
                \App\Filament\Resources\PaymentResource::class,
                \App\Filament\Resources\StockResource::class,
                \App\Filament\Resources\StockMovementResource::class,
                \App\Filament\Resources\RoleResource::class,
                \App\Filament\Resources\DataImportResource::class,
                \App\Filament\Resources\InventoryReportResource::class,
                \App\Filament\Resources\SalesReportResource::class,
                \App\Filament\Resources\StockAnomalyReportResource::class,
                \App\Filament\Resources\NotificationResource::class,
            ])
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->widgets([
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\SalesRevenueChart::class,
                \App\Filament\Widgets\InvoiceStatusChart::class,
                \App\Filament\Widgets\InventoryAlertsWidget::class,
                \App\Filament\Widgets\RecentDeliveryNotesWidget::class,
                \App\Filament\Widgets\PurchasingActivityWidget::class,
                \App\Filament\Widgets\TopSellingProductsWidget::class,
                \App\Filament\Widgets\TopCustomersWidget::class,
            ])
            ->middleware([
                // MINIMAL MIDDLEWARE - only what's needed
                EncryptCookies::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                \App\Http\Middleware\VerifyCsrfToken::class, // Custom CSRF with Livewire exclusion
                SubstituteBindings::class,
            ])
            ->authMiddleware([
                Authenticate::class, // Normal authentication - login required
            ]);
    }
}
