<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    protected $fillable = [
        'user_id',
        'country',
        'city',
        'interests',
        'last_active'
    ];

    protected $casts = [
        'interests' => 'array',
        'last_active' => 'datetime'
    ];
}
