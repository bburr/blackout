<?php declare(strict_types=1);

namespace App\Http\Requests\Round;

class PerformBetAsUserRequest extends PerformBetRequest
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            'auth_user_id' => 'required',
        ]);
    }
}
