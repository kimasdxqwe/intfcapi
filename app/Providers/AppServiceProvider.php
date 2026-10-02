<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
//        Passport::tokensExpireIn(CarbonInterval::day(15));
        Passport::tokensExpireIn(CarbonInterval::minutes(10));

//        Passport::refreshTokensExpireIn(CarbonInterval::days(60));
        Passport::refreshTokensExpireIn(CarbonInterval::minutes(3));

        Passport::tokensCan([
            'wms:zread' => 'Perform Zread',
            'wms:xread' => 'Perform Xread',
            'user:read' => 'Read authenticated profile',
            'wms:interfacing' => 'Manage interfacing on your behalf',
        ]);

        Passport::authorizationView('auth.oauth.authorize');

    }
}
