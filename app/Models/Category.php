<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            // Cari atau buat kategori "Uncategorized"
            $uncategorized = Category::firstOrCreate(['name' => 'Uncategorized']);
    
            // Pindahkan semua produk ke kategori "Uncategorized"
            $category->products()->update(['category_id' => $uncategorized->id]);
        });
    }
}

