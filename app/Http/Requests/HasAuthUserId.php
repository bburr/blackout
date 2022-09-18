<?php

namespace App\Http\Requests;

trait HasAuthUserId
{
    public function getAuthUserId(): string
    {
        return $this->get('auth_user_id');
    }
}
