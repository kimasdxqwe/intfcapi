<?php

namespace App\Providers;

use App\Blueprint\RequestInterface;
use App\Concrete\RequestConcrete;
use Illuminate\Support\ServiceProvider;

class BindingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singletonIf(RequestInterface::class, function($app){

            return new RequestConcrete();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
