<?php

namespace App\Policies;

use App\Models\Company;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CompanyPolicy
{
    /**
     * Determina se o usuário pode visualizar a empresa.
     */
    public function view(?Authenticatable $user, Company $company): bool
    {
        // Apenas a própria empresa pode ver seus dados
        return $user && $user instanceof Company && $user->id === $company->id;
    }

    /**
     * Determina se o usuário pode atualizar a empresa.
     */
    public function update(?Authenticatable $user, Company $company): bool
    {
        // Apenas a própria empresa pode se editar
        return $user && $user instanceof Company && $user->id === $company->id;
    }

    /**
     * Determina se o usuário pode deletar a empresa.
     */
    public function delete(?Authenticatable $user, Company $company): bool
    {
        // Apenas a própria empresa pode se deletar
        return $user && $user instanceof Company && $user->id === $company->id;
    }
}