<?php declare(strict_types=1);

namespace App\Http\Requests\Round;

use App\Http\Requests\Request;

class PerformBet extends Request
{
    public function rules()
    {
        return [
            'bet' => 'required',
            'gameId' => 'required',
        ];
    }
}
