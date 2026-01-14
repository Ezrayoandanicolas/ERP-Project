<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Filament\Support\Facades\FilamentView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'head.end',
            fn () => view('pwa.head')
        );
        // if (request()->getHost() !== 'localhost' && request()->getHost() !== '127.0.0.1') {
        //     URL::forceScheme('https');
        // }
        $isLocal = in_array(request()->getHost(), ['localhost', '127.0.0.1']);

        // Jika bukan localhost ATAU request ditandai HTTPS oleh proxy
        if (! $isLocal || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }
    }
}
