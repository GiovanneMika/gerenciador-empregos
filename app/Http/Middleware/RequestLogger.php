<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $this->logRequest($request, $response, $duration);

        return $response;
    }

    private function logRequest(Request $request, Response $response, float $duration): void
    {
        // Não logar a própria rota de monitoramento
        if (str_starts_with($request->path(), 'monitor')) {
            return;
        }

        $logPath = storage_path('logs/server_requests.log');

        $user = auth('api')->user();
        $userId = $user ? ($user->id . ' (' . ($user->username ?? 'N/A') . ')') : 'guest';
        $userType = $user ? (class_basename($user)) : 'none';

        $requestBody = $request->except(['password']);
        $responseBody = $this->getResponseBody($response);

        $entry = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'method' => $request->method(),
            'path' => '/' . $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
            'user_id' => $userId,
            'user_type' => $userType,
            'request_body' => $requestBody,
            'response_body' => $responseBody,
        ];

        File::append($logPath, json_encode($entry) . "\n");
    }

    private function getResponseBody(Response $response): mixed
    {
        $content = $response->getContent();

        if (empty($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Remove token do log por segurança
            if (isset($decoded['token'])) {
                $decoded['token'] = '[REDACTED]';
            }
            return $decoded;
        }

        return strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
    }
}
