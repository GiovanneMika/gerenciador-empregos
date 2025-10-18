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
        // Retorna EXATAMENTE conforme o protocolo (SEM user_id)
        return [
            'name' => $this->name, // Já está em MAIÚSCULO no banco
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'experience' => $this->experience,
            'education' => $this->education,
        ];
    }
}
