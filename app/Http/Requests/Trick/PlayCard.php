<?php declare(strict_types=1);

namespace App\Http\Requests\Trick;

use App\Http\Requests\Request;

class PlayCard extends Request
{
    public function rules()
    {
        return [
            'game_id' => 'required',
            'card_suit' => 'required',
            'card_value' => 'required',
        ];
    }
}
