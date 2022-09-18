<?php declare(strict_types=1);

namespace App\Http\Requests\Lobby;

use App\Http\Requests\HasAuthUserId;
use App\Http\Requests\Request;

class CreateLobbyRequest extends Request
{
    use HasAuthUserId;
}
