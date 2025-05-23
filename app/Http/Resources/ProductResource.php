<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'images_path' => $this->images_path,
            'images_url' => $this->images_url,
            'category' => new CategoryResource($this->category),
            'user' => new UserResource($this->user),
            'price' => $this->price,
            'is_price_set' => $this->price > 0, 
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}