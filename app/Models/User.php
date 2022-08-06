<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    const CACHE_KEY_USER_ID = 'user-id';

    protected $fillable = [
        'name',
    ];
}
