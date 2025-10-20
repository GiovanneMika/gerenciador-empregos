<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        // Verifica se username já existe ANTES da validação (para retornar 409 conforme protocolo)
        if (User::where('username', $request->username)->exists()) {
            return response()->json(['message' => 'Username already exists'], 409);
        }

        // Validação dos dados conforme o protocolo (sem unique no username)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|max:150',
            'username' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9]+$/', // password SEM underscore (só alfanumérico)
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string|min:10|max:14|regex:/^[0-9]+$/',
            'experience' => 'sometimes|nullable|string|min:10|max:600',
            'education' => 'sometimes|nullable|string|min:10|max:600',
        ]);

        if ($validator->fails()) {
            $details = [];
            foreach ($validator->errors()->messages() as $field => $errors) {
                $details[] = ['field' => $field, 'error' => $errors[0]];
            }
            return response()->json([
                'message' => 'Validation error',
                'code' => 'UNPROCESSABLE',
                'details' => $details
            ], 422);
        }

        User::create([
            'name' => strtoupper($request->name),
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'phone' => $request->phone,
            'experience' => $request->experience,
            'education' => $request->education,
        ]);

        return response()->json(['message' => 'Created'], 201);
    }

    /**
     * Ler dados do usuário.
     */
    public function show(User $user)
    {
        // Verifica a permissão usando a UserPolicy
        try {
            $this->authorize('view', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new UserResource($user);
    }

    /**
     * Editar usuário.
     */
    public function update(Request $request, User $user)
    {
        // Verifica se o usuário está autenticado e se tem permissão
        try {
            $this->authorize('update', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        // Nota: o Laravel já retorna 404 automaticamente se o usuário não existir (Route Model Binding)

        // Validação (note o 'sometimes' para campos opcionais)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:4|max:150',
            'password' => 'sometimes|required|string|min:3|max:20|regex:/^[a-zA-Z0-9]+$/', // password SEM underscore
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string|min:10|max:14|regex:/^[0-9]+$/',
            'experience' => 'sometimes|nullable|string|min:10|max:600',
            'education' => 'sometimes|nullable|string|min:10|max:600',
        ]);

        if ($validator->fails()) {
            $details = [];
            foreach ($validator->errors()->messages() as $field => $errors) {
                $details[] = ['field' => $field, 'error' => $errors[0]];
            }
            return response()->json([
                'message' => 'Validation error',
                'code' => 'UNPROCESSABLE',
                'details' => $details
            ], 422);
        }

        $validatedData = $validator->validated();

        // Regra de negócio: Armazenar nome em maiúsculo
        if (isset($validatedData['name'])) {
            $validatedData['name'] = strtoupper($validatedData['name']);
        }

        // Criptografa a senha se enviada
        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // não sobrescrever senha vazia
        }

        // Campos opcionais vazios devem ser tratados como null
        foreach (['email', 'phone', 'experience', 'education'] as $field) {
            if (isset($validatedData[$field]) && $validatedData[$field] === '') {
                $validatedData[$field] = null;
            }
        }

        $user->update($validatedData);

        return response()->json(['message' => 'User updated successfully'], 200);
    }


    /**
     * Deletar usuário.
     */
    public function destroy(User $user)
    {
        // Verifica a permissão
        try {
            $this->authorize('delete', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
