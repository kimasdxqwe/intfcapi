<?php

namespace App\Http\Middleware;

use App\Blueprint\RequestInterface;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Client;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\ValidateToken;
use Laravel\Passport\Token;

class CheckTokens extends ValidateToken
{
    protected function validate(ScopeAuthorizable $token, string ...$params): void
    {
        _debug('CheckTokens');

        $requestInterface = app(RequestInterface::class);

        if($token instanceof AccessToken){

            //$requestInterface->debug('CheckTokens Before');

            $requestInterface->oAuthContext->resolveAccessToken($token, 0, $params);

//            _debug([
//                'Token revoke' => $requestInterface->oAuthContext->accessToken->revoke()
//            ]);

            //$requestInterface->debug('CheckTokens After');
        }

        foreach ($params as $scope) {
            if ($token->cant($scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
