<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingPayment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_token_id',
        'vendor_token_id',
        'user_id',
        'amount',
        'status',
        'expires_at',
        'created_at',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function clientToken()
    {
        return $this->belongsTo(ClientToken::class);
    }

    public function vendorToken()
    {
        return $this->belongsTo(VendorToken::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
