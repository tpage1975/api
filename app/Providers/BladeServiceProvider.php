<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        resolve(\Illuminate\View\Factory::class)->addExtension('blade.yaml', 'blade');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}