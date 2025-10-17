<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Tenta autenticar o usuário
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Pega usuário autenticado
        $user = auth('api')->user();

        // Claims personalizadas
        $customClaims = [
            'sub' => $user->id,           // ID do usuário
            'username' => $user->username,
            'role' => 'user',
            'exp' => now()->addSeconds(auth('api')->factory()->getTTL() * 60)->timestamp
        ];

        // Regerar token com os claims corretos
        $token = auth('api')->claims($customClaims)->fromUser($user);

        return response()->json([
            'token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'OK']);
    }
}
