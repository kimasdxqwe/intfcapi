<?php

namespace App\Concrete;

use App\Blueprint\RequestInterface;

#[Singleton]
class RequestConcrete implements RequestInterface
{
    public ?OAuthContext $oAuthContext;
    public object $payload;
    public object $filters;
    public object $orders;

    public function __construct()
    {
        $this->oAuthContext = new OAuthContext();
        $this->payload = (object) [];
        $this->filters = (object) [];
        $this->orders = (object) [];
    }

    public function debug($caller): void
    {
        _debug([
            $caller . ' RequestConcrete all' => [
                'oauth' => [
                    'resolved' => $this->oAuthContext->resolved,
                    ...($this->oAuthContext->resolved
                        ? [
                            'route' => [
                                'path' => request()->path(),
                                'scope?' => $this->oAuthContext->scopeFlag ? 'scope' : 'scopes',
                                'scope params' => $this->oAuthContext->scopes,
                            ],
                            'token' => [
                                'token instance' => get_class($this->oAuthContext?->accessToken),
                                ...($this->oAuthContext->resolved
                                    ? $this->oAuthContext->accessToken?->toArray()
                                    : []
                                )
                            ]
                        ]
                        : [])
                ],
                'payload' => $this->payload,
                'filters' => $this->filters,
                'orders' => $this->orders,
            ],
        ]);
    }

    public function debugOAuth($caller): void
    {
        _debug([
            $caller . ' RequestConcrete oauth' => [
                'resolved' => $this->oAuthContext->resolved,
                ...($this->oAuthContext->resolved
                    ? [
                        'route' => [
                            'path' => request()->path(),
                            'scope?' => $this->oAuthContext->scopeFlag ? 'scope' : 'scopes',
                            'scope params' => $this->oAuthContext->scopes,
                        ],
                        'token' => [
                            'token instance' => get_class($this->oAuthContext?->accessToken),
                            ...($this->oAuthContext->resolved
                                ? $this->oAuthContext->accessToken?->toArray()
                                : []
                            )
                        ]
                    ]
                    : []),
            ],
        ]);
    }

    public function debugPayload($caller): void
    {
        _debug([
            $caller . ' RequestConcrete payload' => [
                'payload' => $this->payload,
                'filters' => $this->filters,
                'orders' => $this->orders,
            ],
        ]);
    }

    public function resetPayloadAndFilters(): void
    {
        $this->payload = (object) [];
        $this->filters = (object) [];
        $this->orders = (object) [];
    }
}
