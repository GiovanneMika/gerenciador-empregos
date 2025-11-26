<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use Tymon\JWTAuth\Facades\JWTAuth;

class MultiModelUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        $id = (int) $identifier;
        $role = $this->resolveRoleFromToken();

        return match ($role) {
            'company' => Company::find($id),
            'user' => User::find($id),
            default => User::find($id) ?? Company::find($id),
        };
    }

    public function retrieveByToken($identifier, $token)
    {
        return null; // Não usado pelo JWT
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Não usado pelo JWT
    }

    public function retrieveByCredentials(array $credentials)
    {
        $username = $credentials['username'] ?? null;
        
        if (!$username) {
            return null;
        }

        // Tenta User primeiro, depois Company
        return User::where('username', $username)->first() 
            ?? Company::where('username', $username)->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // Não necessário para este caso
    }

    private function resolveRoleFromToken(): ?string
    {
        try {
            return JWTAuth::parseToken()->getPayload()->get('role');
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
