<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProtocolErrorController extends Controller
{
    public function store(Request $request): Response
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->noContent(401);
        }

        $message = $request->input('message');
        if (!is_string($message) || strlen($message) < 3 || strlen($message) > 1000) {
            return response()->noContent(422);
        }

        $this->logProtocolError($user, $message, $request);

        return response()->noContent(200);
    }

    private function logProtocolError($user, string $message, Request $request): void
    {
        $logPath = storage_path('logs/protocol_errors.log');

        $userType = $user instanceof Company ? 'company' : 'user';
        $timestamp = now()->format('Y-m-d H:i:s');

        $logEntry = sprintf(
            "[%s] %s #%d (%s) reported: %s | IP: %s | User-Agent: %s\n",
            $timestamp,
            strtoupper($userType),
            $user->id,
            $user->username ?? $user->name ?? 'N/A',
            $message,
            $request->ip(),
            $request->userAgent() ?? 'N/A'
        );

        File::append($logPath, $logEntry);
    }
}
