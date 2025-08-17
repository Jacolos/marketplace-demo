<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

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
        // Ustaw domyślną długość string dla MySQL
        Schema::defaultStringLength(191);
        
        // Użyj Tailwind dla paginacji
        Paginator::useTailwind();
        
        // Lub jeśli chcesz użyć własnego widoku:
        // Paginator::defaultView('vendor.pagination.tailwind');
        // Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');
        
        // Rejestracja helpers
        if (file_exists($file = app_path('Helpers/helpers.php'))) {
            require $file;
        }
    }
}