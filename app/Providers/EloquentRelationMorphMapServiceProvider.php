<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class EloquentRelationMorphMapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
        ]);
    }
}
