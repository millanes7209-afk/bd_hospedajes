<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('tenant', function () {
            return (object) [
                'id' => 1,
                'subdominio' => 'ricopollo',
                'nombre' => env('APP_NAME', 'RICO POLLO'),
                'eslogan' => 'Sabor que cruje, pasión que deleita',
                'primary_color' => '#FFE66D',
                'accent_color' => '#E23E1A',
                'logo' => 'assets/ricopollo.svg',
            ];
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
