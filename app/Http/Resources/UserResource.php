<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'email'         => $this->email,
            'role'          => $this->role,
            'phone_number'  => $this->phone_number,
            'is_active'     => $this->is_active,
            'address'       => $this->address,
            'profile_photo' => $this->profile_photo 
                                ? url('uploads/profile_photos/' . $this->profile_photo) 
                                : null,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
