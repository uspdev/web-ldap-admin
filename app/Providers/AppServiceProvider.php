<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Fix para MariaDB ao rodar migrations
        Schema::defaultStringLength(191);

        // Forçar https em produção
        /*
        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        } */
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
