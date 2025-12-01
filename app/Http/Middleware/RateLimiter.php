<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimiter
{
    /**
     * Número máximo de requisições permitidas no intervalo
     */
    private const MAX_REQUESTS = 20;

    /**
     * Intervalo de tempo em segundos (1 minuto)
     */
    private const DECAY_SECONDS = 60;

    /**
     * Número de requisições para considerar como spam (bloqueio)
     */
    private const SPAM_THRESHOLD = 40;

    /**
     * Tempo de bloqueio em segundos (5 minutos)
     */
    private const BLOCK_DURATION = 300;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $blockKey = "blocked:{$ip}";
        $requestKey = "requests:{$ip}";

        // Verifica se o IP está bloqueado
        if (Cache::has($blockKey)) {
            $remainingTime = Cache::get($blockKey) - time();
            
            return response()->json([
                'message' => 'Too many requests. You have been temporarily blocked.',
                'retry_after' => max(0, $remainingTime),
            ], 429)->withHeaders([
                'Retry-After' => max(0, $remainingTime),
                'X-RateLimit-Limit' => self::MAX_REQUESTS,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        // Obtém dados de requisições do IP
        $requestData = Cache::get($requestKey, [
            'count' => 0,
            'first_request' => time(),
        ]);

        $now = time();
        $elapsed = $now - $requestData['first_request'];

        // Reseta o contador se passou o intervalo
        if ($elapsed >= self::DECAY_SECONDS) {
            $requestData = [
                'count' => 1,
                'first_request' => $now,
            ];
        } else {
            $requestData['count']++;
        }

        // Salva os dados atualizados
        Cache::put($requestKey, $requestData, self::DECAY_SECONDS);

        // Verifica se excedeu o limite de spam (bloqueio)
        if ($requestData['count'] >= self::SPAM_THRESHOLD) {
            $blockUntil = $now + self::BLOCK_DURATION;
            Cache::put($blockKey, $blockUntil, self::BLOCK_DURATION);

            // Loga o bloqueio
            $this->logBlock($ip, $requestData['count']);

            return response()->json([
                'message' => 'Too many requests. You have been temporarily blocked.',
                'retry_after' => self::BLOCK_DURATION,
            ], 429)->withHeaders([
                'Retry-After' => self::BLOCK_DURATION,
                'X-RateLimit-Limit' => self::MAX_REQUESTS,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        // Verifica se excedeu o rate limit normal
        if ($requestData['count'] > self::MAX_REQUESTS) {
            $remaining = self::DECAY_SECONDS - $elapsed;

            return response()->json([
                'message' => 'Too many requests. Please slow down.',
                'retry_after' => $remaining,
            ], 429)->withHeaders([
                'Retry-After' => $remaining,
                'X-RateLimit-Limit' => self::MAX_REQUESTS,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        // Processa a requisição normalmente
        $response = $next($request);

        // Adiciona headers de rate limit na resposta
        $remaining = max(0, self::MAX_REQUESTS - $requestData['count']);

        return $response->withHeaders([
            'X-RateLimit-Limit' => self::MAX_REQUESTS,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $requestData['first_request'] + self::DECAY_SECONDS,
        ]);
    }

    /**
     * Loga o bloqueio de IP em arquivo separado
     */
    private function logBlock(string $ip, int $requestCount): void
    {
        $logPath = storage_path('logs/blocked_ips.log');
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $logEntry = "[{$timestamp}] IP bloqueado: {$ip} - Requisições: {$requestCount} em " . self::DECAY_SECONDS . "s - Bloqueio: " . self::BLOCK_DURATION . "s\n";
        
        file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
