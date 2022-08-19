<?php declare(strict_types=1);

namespace App\Http\Requests\Trick;

use App\Http\Requests\Request;

class PlayCard extends Request
{
    public function rules()
    {
        return [
            'gameId' => 'required',
            'cardSuit' => 'required',
            'cardValue' => 'required',
        ];
    }
}
