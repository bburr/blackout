<?php

namespace App\Models;

class User extends Model
{
    const CACHE_KEY_USER_ID = 'user-id';

    protected $fillable = [
        'name',
    ];
}
