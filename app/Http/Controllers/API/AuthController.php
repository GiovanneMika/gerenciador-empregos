<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // O JWT exige que passemos os claims customizados
        $customClaims = [
            'username' => $credentials['username'],
            'role' => 'user'
        ];

        if (!$token = auth('api')->claims($customClaims)->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60 // Pega o tempo de vida do token em segundos
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'OK']);
    }
}
