<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'token_hash',
        'name',
        'description',
        'rate_limit_per_minute',
        'is_active',
        'webhook_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit_per_minute' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function pendingPayments()
    {
        return $this->hasMany(PendingPayment::class);
    }
}
