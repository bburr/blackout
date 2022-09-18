<?php declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CreateUserRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }

    public function getName(): string
    {
        return $this->get('name');
    }
}
