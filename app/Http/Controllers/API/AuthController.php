<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Usa o guard 'api' com o provider multi_model
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Pega o usuário autenticado (pode ser User ou Company)
        $authenticatable = Auth::guard('api')->user();
        
        // Determina o role baseado no tipo do modelo
        $role = $authenticatable instanceof Company ? 'company' : 'user';

        // Claims personalizadas
        $customClaims = [
            'sub' => $authenticatable->id,
            'username' => $authenticatable->username,
            'role' => $role,
        ];

        // Gera token com claims customizados
        $token = JWTAuth::claims($customClaims)->fromUser($authenticatable);

        return response()->json([
            'token' => $token,
            'expires_in' => 3600 // 60 minutos
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'OK']);
    }
}
