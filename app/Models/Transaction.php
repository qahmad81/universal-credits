<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'client_token_id',
        'vendor_token_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_id',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientToken()
    {
        return $this->belongsTo(ClientToken::class);
    }

    public function vendorToken()
    {
        return $this->belongsTo(VendorToken::class);
    }
}
