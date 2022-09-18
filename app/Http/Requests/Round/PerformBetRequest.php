<?php declare(strict_types=1);

namespace App\Http\Requests\Round;

use App\Http\Requests\HasAuthUserId;
use App\Http\Requests\Request;

class PerformBetRequest extends Request
{
    use HasAuthUserId;

    public function rules()
    {
        return [
            'bet' => 'required',
            'gameId' => 'required',
        ];
    }

    public function getBet(): int
    {
        return $this->get('bet');
    }

    public function getGameId(): string
    {
        return $this->get('game_id');
    }
}
