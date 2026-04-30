<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'is_active',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'sort_order' => 'integer',
    ];
}
