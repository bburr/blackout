<?php declare(strict_types=1);

namespace App\Http\Requests\Player;

use App\Http\Requests\Request;

class GetHand extends Request
{
    public function rules()
    {
        return [
            'game_id' => 'required',
        ];
    }
}
