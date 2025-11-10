<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Cadastro de empresa
     */
    public function store(Request $request)
    {
        // Lista de estados válidos do Brasil
        $validStates = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 
            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 
            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
        ];

        // Verifica se nome da empresa já existe ANTES da validação (para retornar 409)
        if (Company::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Company name already exists'], 409);
        }

        // Verifica se username já existe ANTES da validação (para retornar 409)
        if (Company::where('username', $request->username)->exists()) {
            return response()->json(['message' => 'Username already exists'], 409);
        }

        // Validação dos dados conforme o protocolo
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|max:150',
            'business' => 'required|string|min:4|max:150',
            'username' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9]+$/',
            'street' => 'required|string|min:3|max:150',
            'number' => 'required|string|min:1|max:8|regex:/^[0-9]+$/',
            'city' => 'required|string|min:3|max:150',
            'state' => 'required|string|in:' . implode(',', $validStates),
            'phone' => 'required|string|min:10|max:14|regex:/^[0-9]+$/',
            'email' => 'required|email|min:10|max:150',
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

        Company::create([
            'name' => $request->name,
            'business' => $request->business,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'street' => $request->street,
            'number' => $request->number,
            'city' => $request->city,
            'state' => $request->state,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return response()->json(['message' => 'Created'], 201);
    }

    /**
     * Ler dados da empresa
     */
    public function show(Company $company)
    {
        // Verifica a permissão usando a CompanyPolicy
        try {
            $this->authorize('view', $company);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new CompanyResource($company);
    }

    /**
     * Editar empresa
     */
    public function update(Request $request, Company $company)
    {
        // Lista de estados válidos do Brasil
        $validStates = [
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 
            'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 
            'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
        ];

        if (Company::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Company name already exists'], 409);
        }

        // Verifica se a empresa está autenticada e se tem permissão
        try {
            $this->authorize('update', $company);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validação (note o 'sometimes' para campos opcionais)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|min:4|max:150',
            'business' => 'sometimes|required|string|min:4|max:150',
            'password' => 'sometimes|required|string|min:3|max:20|regex:/^[a-zA-Z0-9]+$/',
            'street' => 'sometimes|required|string|min:3|max:150',
            'number' => 'sometimes|required|string|min:1|max:8|regex:/^[0-9]+$/',
            'city' => 'sometimes|required|string|min:3|max:150',
            'state' => 'sometimes|required|string|in:' . implode(',', $validStates),
            'phone' => 'sometimes|required|string|min:10|max:14|regex:/^[0-9]+$/',
            'email' => 'sometimes|required|email|min:10|max:150',
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

        // Criptografa a senha se enviada
        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // não sobrescrever senha vazia
        }

        $company->update($validatedData);

        return response()->json(['message' => 'Updated'], 200);
    }

    /**
     * Deletar empresa
     */
    public function destroy(Company $company)
    {
        // Verifica a permissão
        try {
            $this->authorize('delete', $company);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // TODO: Quando implementarmos vagas, verificar se há vagas ativas
        // if ($company->jobs()->where('active', true)->exists()) {
        //     return response()->json(['message' => 'Cannot delete company with active jobs'], 409);
        // }

        $company->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);
    }
}