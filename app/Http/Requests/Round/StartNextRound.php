<?php declare(strict_types=1);

namespace App\Http\Requests\Round;

use App\Http\Requests\Request;

class StartNextRound extends Request
{
    public function rules()
    {
        return [
            'game_id' => 'required',
        ];
    }
}
