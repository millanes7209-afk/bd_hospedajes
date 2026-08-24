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
                'subdominio' => 'monaka',
                'nombre' => env('APP_NAME', 'Salteñería Monaka'),
                'eslogan' => 'Las salteñas más deliciosas de la ciudad',
                'primary_color' => '#FFE66D',
                'accent_color' => '#E23E1A',
                'logo' => 'assets/logo.svg',
            ];
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('pedidos')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            // Ignorar silenciosamente si no hay conexion DB
        }
    }
}
