<?php

namespace App\Http\Middleware;

use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Http\Middleware\ValidateToken;

class EnsureClientIsCustomOwner extends ValidateToken
{
    /**
     * @throws AuthenticationException
     */
    protected function validate(ScopeAuthorizable $token, string ...$params): void
    {
        if (
            $token instanceof AccessToken
            && ! is_null($token->oauth_user_id)
            && $token->oauth_user_id !== $token->oauth_client_id
        ) {
            throw new AuthenticationException;
        }
    }
}
