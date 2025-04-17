<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'total_price',
        'status',
        'payment_status',
        'payment_url',
        'midtrans_response',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'total_price' => 'integer',
    ];

    protected $hidden = [
        'midtrans_response'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getStatusAttribute($value)
    {
        return strtolower($value);
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}