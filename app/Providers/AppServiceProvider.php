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
        $isProductionEnvironment = app()->environment('production', 'prod');

        if($isProductionEnvironment){

            Passport::tokensExpireIn(CarbonInterval::day(10));
            Passport::refreshTokensExpireIn(CarbonInterval::days(60));

        } else {

            Passport::tokensExpireIn(CarbonInterval::minutes(config('passport.dev_passport_token_lifetime')));
            Passport::refreshTokensExpireIn(CarbonInterval::minutes(config('passport.dev_passport_refresh_token_lifetime')));
        }

        Passport::personalAccessTokensExpireIn(CarbonInterval::months(3));

        Passport::tokensCan([
            'user:read' => 'Read authenticated profile',
            'wms:interfacing' => 'Manage interfacing on your behalf',
        ]);

        Passport::authorizationView('auth.oauth.authorize');

    }
}
