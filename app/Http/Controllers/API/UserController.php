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
        // Validação dos dados conforme o protocolo
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|max:150',
            'username' => 'required|string|min:3|max:20|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'sometimes|nullable|email',
            'phone' => 'sometimes|nullable|string|min:10|max:14|regex:/^[0-9]+$/',
            'experience' => 'sometimes|nullable|string|min:10|max:600',
            'education' => 'sometimes|nullable|string|min:10|max:600',
        ]);

        if ($validator->fails()) {
            // A customização do erro 422 faremos no Passo 4
            return response()->json($validator->errors(), 422);
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
        // Verifica a permissão usando a UserPolicy que criamos
        $this->authorize('view', $user);

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

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validação (note o 'sometimes' para campos opcionais)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:4|max:150',
            'password' => 'sometimes|required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $user->id,
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

        return new UserResource($user);
    }


    /**
     * Deletar usuário.
     */
    public function destroy(User $user)
    {
        // Verifica a permissão
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
