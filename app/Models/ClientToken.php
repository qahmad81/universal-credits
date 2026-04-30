<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'token_hash',
        'name',
        'limit_balance',
        'final_balance',
        'pending_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'limit_balance' => 'integer',
        'final_balance' => 'integer',
        'pending_balance' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function pendingPayments()
    {
        return $this->hasMany(PendingPayment::class);
    }
}
