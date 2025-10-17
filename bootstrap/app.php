<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- Importe a classe Request
use Illuminate\Validation\ValidationException; // <-- Importe a classe ValidationException

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
            \Illuminate\Http\Middleware\HandleCors::class,
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
        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, $request) {
            return response()->json(['message' => 'Invalid token'], 401);
        });

        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, $request) {
            return response()->json(['message' => 'Token expired'], 401);
        });

        $exceptions->renderable(function (\Tymon\JWTAuth\Exceptions\JWTException $e, $request) {
            return response()->json(['message' => 'Token not provided'], 401);
        });
    })->create();
