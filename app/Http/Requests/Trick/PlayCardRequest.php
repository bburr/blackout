<?php declare(strict_types=1);

namespace App\Http\Requests\Trick;

use App\Http\Requests\HasAuthUserId;
use App\Http\Requests\Request;

class PlayCardRequest extends Request
{
    use HasAuthUserId;

    public function rules()
    {
        return [
            // todo honestly figure out if you want camelCase or snake_case for user input fields [snake_case]
            'gameId' => 'required',
            'cardSuit' => 'required',
            'cardValue' => 'required',
        ];
    }

    public function getCardSuit(): string
    {
        return $this->get('cardSuit');
    }

    public function getCardValue(): int
    {
        return $this->get('cardValue');
    }

    public function getGameId(): string
    {
        return $this->get('gameId');
    }
}
