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
<<<<<<< HEAD
            'images_path' => $this->images_path,
            'images_url' => $this->images_url,
=======
            'image' => $this->image ? asset('storage/' . $this->image) : null,
>>>>>>> d1d3ebd3c1795f4b847c9b61b7441c20a5eefbe5
            'category' => new CategoryResource($this->category),
            'user' => new UserResource($this->user),
            'price' => $this->price,
            'is_price_set' => $this->price > 0, 
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}