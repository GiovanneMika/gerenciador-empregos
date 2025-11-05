<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;
use App\Models\Company;

class MultiModelUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        // Tenta buscar primeiro em User, depois em Company
        return User::find($identifier) ?? Company::find($identifier);
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
        return \Hash::check($credentials['password'], $user->password);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // Não necessário para este caso
    }
}
