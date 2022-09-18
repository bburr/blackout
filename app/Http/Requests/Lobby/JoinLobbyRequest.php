<?php declare(strict_types=1);

namespace App\Http\Requests\Lobby;

use App\Http\Requests\HasAuthUserId;
use App\Http\Requests\Request;

class JoinLobbyRequest extends Request
{
    use HasAuthUserId;

    public function rules()
    {
        return [
            'invite_code' => 'required',
        ];
    }

    public function getInviteCode(): string
    {
        return $this->get('invite_code');
    }
}
