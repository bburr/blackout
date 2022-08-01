<?php declare(strict_types=1);

namespace App\Http\Requests\Lobby;

use App\Http\Requests\Request;

class JoinLobby extends Request
{
    public function rules()
    {
        return [
            'invite_code' => 'required',
        ];
    }
}
