<?php

namespace App\Http\Middleware;

use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use Laravel\Passport\Http\Middleware\ValidateToken;

class EnsureClientIsCustomOwner extends ValidateToken
{
    /**
     * @throws AuthenticationException
     */
    protected function validate(ScopeAuthorizable $token, string ...$params): void
    {
        _debug('EnsureClientIsCustomOwner');

        if (
            $token instanceof AccessToken
            && ! is_null($token->oauth_user_id)
            && $token->oauth_user_id !== $token->oauth_client_id
        ) {
            throw new AuthenticationException;
        }

        foreach ($params as $scope) {
            if ($token->cant($scope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
