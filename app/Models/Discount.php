<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'code', 'percentage', 'expires_at', 'created_by'
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
