<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trick\PlayCardAsUserRequest;
use App\Http\Requests\Trick\PlayCardRequest;
use App\Jobs\Trick\PlayCard;
use Illuminate\Support\Facades\Bus;

class TrickController extends Controller
{
    public function playCard(PlayCardRequest $request): void
    {
        Bus::dispatch(new PlayCard($request->getGameId(), $request->getAuthUserId(), $request->getCardSuit(), $request->getCardValue()));
    }

    public function playCardAsUser(PlayCardAsUserRequest $request): void
    {
        $this->playCard($request);
    }
}
