<?php
namespace SavanRathod\ArtisanCompass;

use Illuminate\Support\ServiceProvider;

class ArtisanCompassServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'artisan-compass');
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/artisan-compass'),
        ], 'public');
    }

    public function register()
    {
        //
    }
}
