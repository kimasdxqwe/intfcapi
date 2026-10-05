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
        AccessToken $accessToken,
        int $scopeFlag,
        $scopes = [],
    ): void {

        $this->accessToken = $accessToken;
        $this->scopeFlag = $scopeFlag;
        $this->scopes = $scopes;

        $this->resolved = $accessToken instanceof AccessToken;
    }
}
