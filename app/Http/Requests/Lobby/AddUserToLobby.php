<?php declare(strict_types=1);

namespace App\Http\Requests\Lobby;

use App\Http\Requests\Request;

class AddUserToLobby extends Request
{
    public function rules()
    {
        return [
            'lobby_id' => 'required_without:invite_code',
            'invite_code' => 'required_without:lobby_id',
            'user_id' => 'required',
        ];
    }

    public function getInviteCode(): ?string
    {
        return $this->get('invite_code');
    }

    public function getLobbyId(): ?string
    {
        return $this->get('lobby_id');
    }

    public function getUserId(): string
    {
        return $this->get('user_id');
    }
}
