<?php

namespace App\Http\Middleware;

use App\Blueprint\RequestInterface;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\ValidateToken;

class CheckTokens extends ValidateToken
{
    protected function validate(ScopeAuthorizable $token, string ...$params): void
    {
        $debugEnabled = false;

        if($debugEnabled){
            _debug('CheckTokens');
        }

        $requestInterface = app(RequestInterface::class);

        if($token instanceof AccessToken){

            if($debugEnabled){
                $requestInterface->debug('CheckTokens Before');
            }

            $requestInterface->oAuthContext->resolveAccessToken($token, 0, $params);

            if($debugEnabled){
                $requestInterface->debug('CheckTokens After');
            }
        }

        foreach ($params as $scope) {
            if ($token->cant($scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
