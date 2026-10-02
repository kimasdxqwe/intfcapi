<?php

namespace App\Http\Middleware;

use App\Blueprint\RequestInterface;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\ValidateToken;

class CheckToken extends ValidateToken
{
    protected function validate(ScopeAuthorizable $token, string ...$params): void
    {
        _debug('CheckToken');

        $requestInterface = app(RequestInterface::class);

        if($token instanceof AccessToken){

            //$requestInterface->debug('CheckToken Before');

            $requestInterface->oAuthContext->resolveAccessToken(1, $params, $token);

            _debug([
                'Token' => $requestInterface->oAuthContext->accessToken
            ]);

            //$requestInterface->debug('CheckToken After');
        }

        foreach ($params as $scope) {
            if ($token->can($scope)) {
                return;
            }
        }

        throw new MissingScopeException($params);
    }
}
