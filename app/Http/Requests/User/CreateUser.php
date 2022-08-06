<?php declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class CreateUser extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }
}
