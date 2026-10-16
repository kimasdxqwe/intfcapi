<?php

namespace App\Http\Middleware;

use App\Blueprint\RequestInterface;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\ValidateToken;

class CheckTokenForAnyScope extends ValidateToken
{
    protected function validate(ScopeAuthorizable $token, string ...$params): void
    {
        $debugEnabled = false;

        if($debugEnabled){
            _debug('CheckTokenForAnyScope');
        }

        $requestInterface = app(RequestInterface::class);

        if($token instanceof AccessToken){

            if($debugEnabled){
                $requestInterface->debug('CheckTokenForAnyScope Before');
            }

            $requestInterface->oAuthContext->resolveAccessToken($token, 1, $params);

            if($debugEnabled){
                _debug([
                    'Token' => $requestInterface->oAuthContext->accessToken
                ]);

                $requestInterface->debug('CheckTokenForAnyScope After');
            }
        }

        foreach ($params as $scope) {
            if ($token->can($scope)) {
                return;
            }
        }

        throw new MissingScopeException($params);
    }
}
