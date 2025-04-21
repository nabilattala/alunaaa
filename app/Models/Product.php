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
        'video_url',
        'image',
        'category_id',
        'user_id',
        'price',
        'status',
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

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }


    // Event untuk memastikan harga hanya 0 jika bukan admin yang menambahkan
    public static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!isset($product->price) || (auth()->check() && auth()->user()->role !== 'admin')) {
                $product->price = 0;
            }
        });

        static::updating(function ($product) {
            \Log::info('Updating product', [
                'product_id' => $product->id,
                'new_price' => $product->price,
                'role' => auth()->user()->role,
            ]);
        });
    }

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

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

}
