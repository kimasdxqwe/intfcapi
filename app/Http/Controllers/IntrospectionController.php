<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class IntrospectionController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->input('token');

        if (! $token) {
            return response()->json(['active' => false]);
        }

        return response()->json($this->introspect($token) ?? ['active' => false]);
    }

    private function introspect(string $token): ?array
    {
        try {

            $config = Configuration::forAsymmetricSigner(
                new Sha256(),
                InMemory::plainText('not-used'),
                InMemory::file(Passport::keyPath('oauth-public.key'))
            );

            $jwt = $config->parser()->parse($token);

            if (! $config->validator()->validate($jwt, new SignedWith($config->signer(), $config->verificationKey()))) {
                return null;
            }

            $claims = $jwt->claims();

            if ($claims->get('exp') <= now()->toDateTimeImmutable()) {
                return null;
            }

            $oAuthToken = Token::find($claims->get('jti'));

            if (! $oAuthToken) {
                return null;
            }

            if ($oAuthToken->revoked) {
                return null;
            }

            return [
                'active'     => true,
                'scope'      => implode(' ', $claims->get('scopes', [])),
                'client_id'  => $oAuthToken->client_id,
                'sub'        => $claims->get('sub'),
                'token_type' => 'Bearer',
                'exp'        => $claims->get('exp')->getTimestamp(),
                'iat'        => $claims->get('iat')->getTimestamp(),
                'aud'        => $claims->get('aud'),
                'jti'        => $claims->get('jti'),
            ];

        } catch (\Throwable $e) {

            return null;
        }
    }
}
