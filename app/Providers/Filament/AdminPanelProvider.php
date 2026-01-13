<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
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
use App\Traits\FilamentLivewireRouteBinder;

class AdminPanelProvider extends PanelProvider
{
    use FilamentLivewireRouteBinder;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('superadmin')
            ->path('superadmin')
            ->login()
            ->sidebarFullyCollapsibleOnDesktop()
            // ->registration()
            // ->passwordReset()
            // ->emailVerification()
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class ,
                \App\Filament\Widgets\SalesStats::class,
                \App\Filament\Widgets\SalesChart::class,
                \App\Filament\Widgets\TopProducts::class,
                \App\Filament\Widgets\ExpenseStats::class,
                // Widgets\FilamentInfoWidget::class,
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
                'panel.role:superadmin,superadmin',
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('superadmin');

    }

    public function register(): void
    {
        parent::register();

        $this->app->afterResolving(\Filament\Panel::class, function (Panel $panel) {

            // Cek apakah property id sudah diset
            if (! property_exists($panel, 'id') || empty($panel->id)) {
                return; // Skip panel yang belum final
            }

            if ($panel->id === 'superadmin') {
                $this->registerLivewireRoutes($panel);
            }
        });
    }

}
