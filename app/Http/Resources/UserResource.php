<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Aqui definimos EXATAMENTE o que vai no JSON de resposta.
        return [
            'user_id' => $this->id, // Pega o ID do usuário
            'name' => strtoupper($this->name), // Pega o nome e aplica a regra de MAIÚSCULO
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'experience' => $this->experience,
            'education' => $this->education,
        ];
    }
}
