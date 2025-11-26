<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProtocolErrorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:3|max:1000',
        ]);

        if ($validator->fails()) {
            $details = [];
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $details[] = ['field' => $field, 'error' => $message];
                }
            }

            return response()->json([
                'message' => 'Validation error',
                'code' => 'UNPROCESSABLE',
                'details' => $details,
            ], 422);
        }

        $payload = $validator->validated();

        Log::warning('Client protocol error', [
            'user_id' => $user->id,
            'user_type' => $user::class,
            'message' => $payload['message'],
        ]);

        return response()->json(['message' => 'Logged']);
    }
}
