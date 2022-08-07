<?php declare(strict_types=1);

namespace App\Jobs;

use App\Exceptions\InvalidCardForPlayException;
use App\State\CardState;
use App\State\GameState;
use App\State\PlayerState;
use Illuminate\Support\Facades\Bus;

class MakePlayForNextPlayer
{
    public function __construct(protected GameState $gameState, protected PlayerState $playerState, protected CardState $cardState)
    {
    }

    /**
     * @throws InvalidCardForPlayException
     */
    public function handle(): void
    {
        $index = $this->playerState->getHand()->search($this->cardState);

        if ($index === false) {
            throw new InvalidCardForPlayException('You do not have that card in your hand');
        }

        $leadingCard = $this->gameState->getCurrentRound()->getCurrentTrick()->getLeadingCard();

        if ($leadingCard !== null && $leadingCard->getSuitKey() !== $this->cardState->getSuitKey()
            && $this->playerState->getHand()->filter(fn (CardState $cardState) => $cardState->getSuitKey() === $leadingCard->getSuitKey())->isNotEmpty()) {
            throw new InvalidCardForPlayException('You must play the same suit as the leading card');
        }

        $this->gameState->getCurrentRound()->makePlayForNextPlayer($this->cardState);
        $this->playerState->getHand()->forget($index);
        $playerIndex = $this->gameState->getCurrentRound()->getNextPlayerIndexToPlay();
        $this->gameState->getCurrentRound()->setNextPlayerIndexToPlay($this->gameState->advancePlayerIndexUntilLeadingPlayer($playerIndex));

        if ($this->gameState->getCurrentRound()->getCurrentTrick()->isTrickDone($this->gameState->getPlayers()->count())) {
            Bus::dispatch(new NextTrick($this->gameState));
        }
    }
}
