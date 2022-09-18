<?php declare(strict_types=1);

namespace App\Http\Requests\Player;

use App\Http\Requests\HasAuthUserId;
use App\Http\Requests\Request;

class GetHandRequest extends Request
{
    use HasAuthUserId;

    public function rules()
    {
        return [
            'game_id' => 'required',
        ];
    }

    public function getGameId(): string
    {
        return $this->get('game_id');
    }
}
