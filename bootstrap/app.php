<?php

use App\Concrete\FractalTransformer;
use App\Concrete\JsonResponseScaffolder;
use App\Facades\ResponseJson;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as RequestFacade;;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withBindings([
        'response_json' => fn() => new JsonResponseScaffolder(),
        'fractal' => fn() => new FractalTransformer(),
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->encryptCookies([
            'refresh_token'
        ]);

        $middleware->alias([
            'ensure_client_is_resource_owner' => \App\Http\Middleware\EnsureClientIsCustomOwner::class,

            //Custom Laravel\Passport\Http\Middleware\CheckToken
            'scopes' => \App\Http\Middleware\CheckTokens::class,
            //Custom Laravel\Passport\Http\Middleware\CheckTokenForAnyScope
            'scope' => \App\Http\Middleware\CheckToken::class,

            //Combinator of auth:api and ensure_client_is_resource_owner
            'ensure_user_or_client' => \App\Http\Middleware\EnsureUserOrClientOwner::class
        ]);

        $middleware->append([
            \App\Http\Middleware\RequestBoot::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (HttpRequest $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->dontReport([]);

        $exceptions->stopIgnoring([HttpException::class]);

        $exceptions->report(function(Throwable $throwable) {})->stop();

        $exceptions->render(function(Throwable $throwable){

            _debug([
                ('thrown') => get_class($throwable),
                'Exception instance?' => ($throwable instanceof Exception ? 'TRUE' : 'FALSE'),
                'Error instance?' => ($throwable instanceof Error ? 'TRUE' : 'FALSE'),
                'message' => $throwable->getMessage(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'request' => RequestFacade::url(),
            ]);

            $logExempt = _is_instance_of_any($throwable, [
                AccessDeniedHttpException::class,
                AuthenticationException::class,
            ]);

            if(!$logExempt){

                $logAction = _is_instance_of_any($throwable, [
                    ValidationException::class
                ]) ? 'notice' : 'error';

                Log::channel('error')->{$logAction}([
                    ('thrown') => get_class($throwable),
                    'Exception instance?' => ($throwable instanceof Exception ? 'TRUE' : 'FALSE'),
                    'Error instance?' => ($throwable instanceof Error ? 'TRUE' : 'FALSE'),
                    'message' => $throwable->getMessage(),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'request' => RequestFacade::url(),
                ]);
            }

            if($throwable instanceof Error){
                abort(500);
            }

            // Handle the redirect case separately, before the JSON match block
            if ($throwable instanceof AuthenticationException
                && !RequestFacade::is('api/*')
                && !RequestFacade::expectsJson()
            ) {
                return redirect()->guest(route('login'));
            }

            if($throwable instanceof Exception){
                $render = match(true){
                    $throwable instanceof NotFoundHttpException,
                    $throwable instanceof RecordsNotFoundException => ResponseJson::notFoundResponse(),
                    $throwable instanceof BackedEnumCaseNotFoundException,
                    $throwable instanceof ModelNotFoundException => ResponseJson::notFoundResponse($throwable->getMessage()),
                    $throwable instanceof AuthorizationException && !$throwable->hasStatus() => ResponseJson::responseByCode(Response::HTTP_FORBIDDEN),
                    $throwable instanceof SuspiciousOperationException => ResponseJson::notFoundResponse('Bad hostname provided.'),
                    $throwable instanceof TokenMismatchException => ResponseJson::notAcceptableResponse(),
                    $throwable instanceof AuthenticationException => ResponseJson::unauthorizedResponse($throwable->getMessage()),
                    $throwable instanceof ValidationException => ResponseJson::validationErrorResponse($throwable->errors(), $throwable->getMessage()),
                    $throwable instanceof ThrottleRequestsException => ResponseJson::tooManyRequestsResponse(),
                    $throwable instanceof MethodNotAllowedHttpException => ResponseJson::methodNotAllowedResponse(),
                    $throwable instanceof InvalidArgumentException => ResponseJson::validationErrorResponse([], $throwable->getMessage()),
                    $throwable instanceof InvalidSignatureException,
                    $throwable instanceof AccessDeniedHttpException => ResponseJson::forbiddenResponse($throwable->getMessage()),
                    default => $throwable,
                };

                if($render instanceof \Illuminate\Http\JsonResponse){
                    return $render;
                }
            }

            return ResponseJson::serverErrorResponse();
        });
    })->create();
