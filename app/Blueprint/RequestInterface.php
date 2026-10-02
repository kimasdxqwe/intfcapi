<?php

namespace App\Blueprint;

use Laravel\Passport\AccessToken;

/**
 * @property AccessToken $token
 * @property array $scopes
 * @property object $payload
 * @property object $filters
 * @property object $orders
 **/
interface RequestInterface
{
    public function debug($caller): void;

    public function debugOAuth($caller): void;

    public function debugPayload($caller): void;

    public function resetPayloadAndFilters(): void;
}
