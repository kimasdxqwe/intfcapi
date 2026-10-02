<?php

namespace App\Concrete;

use Laravel\Passport\AccessToken;
use Laravel\Passport\Token;

class OAuthContext
{
    public bool $resolved = false;

    public ?AccessToken $accessToken = null;

    public int $scopeFlag = 0;

    public array $scopes = [];

    public function __construct()
    {
        $this->accessToken = new AccessToken();
    }

    public function resolveAccessToken(
        int $scopeFlag,
        $scopes = [],
        AccessToken $accessToken
    ): void {
        $this->scopeFlag = $scopeFlag;
        $this->scopes = $scopes;
        $this->accessToken = $accessToken;
        $this->resolved = $accessToken instanceof AccessToken;
    }
}
