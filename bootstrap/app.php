<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- Importe a classe Request
use Illuminate\Validation\ValidationException; // <-- Importe a classe ValidationException
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: base_path('routes/api.php'), // Forma mais explícita de apontar o arquivo
        apiPrefix: '/', // DIZEMOS AO LARAVEL PARA NÃO USAR PREFIXO
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
         //   \App\Http\Middleware\RateLimiter::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\RequestLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ADICIONE ESTE CÓDIGO AQUI DENTRO
        $exceptions->renderable(
            function (ValidationException $e, Request $request) {
                // Garante que esta lógica só se aplique às rotas da sua API
                if ($request->is('*')) {
                    $details = [];
                    foreach ($e->errors() as $field => $error) {
                        // Pega apenas a primeira mensagem de erro para cada campo
                        $details[] = ['field' => $field, 'error' => $error[0]];
                    }

                    return response()->json([
                        'message'   => 'Validation error',
                        'code'      => 'UNPROCESSABLE',
                        'details'   => $details
                    ], 422); // HTTP Status 422
                }
            }
        );
        // Tratamento de exceções JWT conforme protocolo
        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, $request) {
            return response()->json(['message' => 'Invalid Token'], 401);
        });

        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, $request) {
            return response()->json(['message' => 'Invalid Token'], 401); // Token expirado é considerado inválido
        });

        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\JWTException $e, $request) {
            return response()->json(['message' => 'Invalid Token'], 401);
        });

        // Tratamento de AuthenticationException (evita redirect para /login)
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('*')) {
                return response()->json(['message' => 'Invalid Token'], 401);
            }
        });

        // Customiza erro 404 quando Model não é encontrado (ex: User não existe)
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            // Verifica se é um User que não foi encontrado
            if ($e->getModel() === 'App\\Models\\User') {
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json(['message' => 'Resource not found'], 404);
        });
    })->create();
