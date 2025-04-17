<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'profile_photo_url' => $this->profile_photo
                ? (str_starts_with($this->profile_photo, 'http') 
                ? $this->profile_photo 
                : url('uploads/profile_photos/' . $this->profile_photo))
                : null,
        ];
    }


}
