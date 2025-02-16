<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'url',
        'image',
        'category_id',
        'user_id',
        'price',
        'status'
    ];

    protected $casts = [
        'price' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Event untuk menangani penghapusan kategori
    public static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->category_id) {
                $uncategorized = Category::firstOrCreate(['name' => 'Uncategorized']);
                $product->category_id = $uncategorized->id;
            }
        });
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('category', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('user', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    // Scope untuk filter
    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['category_id'] ?? false, function ($q, $category_id) {
            $q->where('category_id', $category_id);
        })->when($filters['min_price'] ?? false, function ($q, $min_price) {
            $q->where('price', '>=', $min_price);
        })->when($filters['max_price'] ?? false, function ($q, $max_price) {
            $q->where('price', '<=', $max_price);
        })->when($filters['user_id'] ?? false, function ($q, $user_id) {
            $q->where('user_id', $user_id);
        })->when($filters['status'] ?? false, function ($q, $status) {
            $q->where('status', $status);
        });
    }
}
