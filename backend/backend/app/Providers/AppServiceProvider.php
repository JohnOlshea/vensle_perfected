<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SimilarProductService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SimilarProductService::class, function ($app) {
            return new SimilarProductService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
	    // Include the constants file
	    config(['constants' => require app_path('Constants/constants.php')]);
    }
}
