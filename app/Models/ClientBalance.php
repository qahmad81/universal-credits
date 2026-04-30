<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBalance extends Model
{
    protected $fillable = [
        'user_id',
        'final_balance',
        'pending_balance',
    ];

    protected $casts = [
        'final_balance' => 'integer',
        'pending_balance' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
