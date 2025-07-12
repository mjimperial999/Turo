<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'image' => $this->image?->image ? base64_encode($this->image?->image) : null,
            'profile_pic' => $this->profile_pic,
            'agreed_to_terms' => $this->agreed_to_terms,
            'requires_password_change' => $this->requires_password_change,
        ];
    }
}
