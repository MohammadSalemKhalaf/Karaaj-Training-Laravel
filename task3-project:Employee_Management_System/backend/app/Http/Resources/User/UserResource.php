<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'role' => [
                'id' => $this->role?->id,
                'name' => $this->role?->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}