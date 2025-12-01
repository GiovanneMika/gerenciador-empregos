<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MonitorController extends Controller
{
    public function index()
    {
        return view('monitor');
    }

    public function logs(): JsonResponse
    {
        $logPath = storage_path('logs/server_requests.log');

        if (!File::exists($logPath)) {
            return response()->json(['logs' => []]);
        }

        $lines = array_filter(explode("\n", File::get($logPath)));
        $logs = [];

        // Pega as últimas 100 entradas
        $lines = array_slice($lines, -100);

        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if ($decoded) {
                $logs[] = $decoded;
            }
        }

        return response()->json(['logs' => array_reverse($logs)]);
    }

    public function clearLogs(): JsonResponse
    {
        $logPath = storage_path('logs/server_requests.log');

        if (File::exists($logPath)) {
            File::delete($logPath);
        }

        return response()->json(['message' => 'Logs cleared']);
    }

    public function activeUsers(): JsonResponse
    {
        // Busca tokens válidos baseado em atividade recente (últimos 60 min)
        $logPath = storage_path('logs/server_requests.log');

        $activeUsers = [];
        $seenUsers = [];

        if (File::exists($logPath)) {
            $lines = array_filter(explode("\n", File::get($logPath)));
            $cutoff = now()->subMinutes(60);

            foreach (array_reverse($lines) as $line) {
                $entry = json_decode($line, true);

                if (!$entry || $entry['user_type'] === 'none') {
                    continue;
                }

                $timestamp = \Carbon\Carbon::parse($entry['timestamp']);
                if ($timestamp->lt($cutoff)) {
                    break;
                }

                $userKey = $entry['user_type'] . ':' . $entry['user_id'];

                if (!isset($seenUsers[$userKey])) {
                    $seenUsers[$userKey] = true;

                    $activeUsers[] = [
                        'user_id' => $entry['user_id'],
                        'user_type' => $entry['user_type'],
                        'last_activity' => $entry['timestamp'],
                        'last_action' => $entry['method'] . ' ' . $entry['path'],
                        'ip' => $entry['ip'],
                    ];
                }

                if (count($activeUsers) >= 50) {
                    break;
                }
            }
        }

        return response()->json(['users' => $activeUsers]);
    }

    public function stats(): JsonResponse
    {
        $logPath = storage_path('logs/server_requests.log');

        $stats = [
            'total_requests' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'avg_response_time' => 0,
            'requests_by_method' => [],
            'requests_by_status' => [],
        ];

        if (!File::exists($logPath)) {
            return response()->json($stats);
        }

        $lines = array_filter(explode("\n", File::get($logPath)));
        $totalTime = 0;

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry) continue;

            $stats['total_requests']++;
            $totalTime += $entry['duration_ms'] ?? 0;

            $status = $entry['status'] ?? 0;
            if ($status >= 200 && $status < 400) {
                $stats['success_count']++;
            } else {
                $stats['error_count']++;
            }

            $method = $entry['method'] ?? 'UNKNOWN';
            $stats['requests_by_method'][$method] = ($stats['requests_by_method'][$method] ?? 0) + 1;

            $stats['requests_by_status'][$status] = ($stats['requests_by_status'][$status] ?? 0) + 1;
        }

        if ($stats['total_requests'] > 0) {
            $stats['avg_response_time'] = round($totalTime / $stats['total_requests'], 2);
        }

        return response()->json($stats);
    }
}
