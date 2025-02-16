<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'title'
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
