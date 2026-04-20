<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudAttempt extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'payload',
        'email',
        'phone',
        'reason',
        'blocked',
    ];

    protected $casts = [
        'payload' => 'array',
        'blocked' => 'boolean',
    ];
}
