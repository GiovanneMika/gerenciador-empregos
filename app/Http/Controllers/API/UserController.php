<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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
        // Verifica a permissão
        $this->authorize('update', $user);

        // Validação (note o 'sometimes' para campos opcionais)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:4|max:150',
            'password' => 'sometimes|required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'sometimes|nullable|email|unique:users,email,'.$user->id,
            'phone' => 'sometimes|nullable|string|min:10|max:14|regex:/^[0-9]+$/',
            'experience' => 'sometimes|nullable|string|min:10|max:600',
            'education' => 'sometimes|nullable|string|min:10|max:600',
        ]);

        if ($validator->fails()) {
            // A customização do erro 422 que já fizemos vai cuidar disso
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();
        
        // Regra de negócio: Armazenar nome em maiúsculo
        if(isset($validatedData['name'])) {
            $validatedData['name'] = strtoupper($validatedData['name']);
        }
        
        // Criptografa a senha se ela foi enviada
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
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
