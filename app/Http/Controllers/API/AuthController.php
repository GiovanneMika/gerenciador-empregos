<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $credentials['username'];
        $password = $credentials['password'];

        // Primeiro tenta encontrar um usuário
        $user = User::where('username', $username)->first();
        if ($user && Hash::check($password, $user->password)) {
            // Claims personalizadas para usuário
            $customClaims = [
                'sub' => $user->id,
                'username' => $user->username,
                'role' => 'user',
                'exp' => now()->addMinutes(60)->timestamp
            ];

            // Gera token
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            return response()->json([
                'token' => $token,
                'expires_in' => 3600 // 60 minutos
            ]);
        }

        // Se não achou usuário, tenta encontrar uma empresa
        $company = Company::where('username', $username)->first();
        if ($company && Hash::check($password, $company->password)) {
            // Claims personalizadas para empresa
            $customClaims = [
                'sub' => $company->id,
                'username' => $company->username,
                'role' => 'company',
                'exp' => now()->addMinutes(60)->timestamp
            ];

            // Gera token
            $token = JWTAuth::claims($customClaims)->fromUser($company);

            return response()->json([
                'token' => $token,
                'expires_in' => 3600 // 60 minutos
            ]);
        }

        // Se não achou nem usuário nem empresa
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'OK']);
    }
}
