<?php declare(strict_types=1);

namespace App\State;

use App\Models\User;
use Illuminate\Support\Collection;

class PlayerState extends AbstractState
{
    /** @var Collection<CardState> */
    protected Collection $hand;

    public function __construct(protected User $user)
    {
        $this->hand = collect();
    }

    public function addToHand(CardState $cardState)
    {
        $this->hand->add($cardState);
    }

    public function getHand(): Collection
    {
        return $this->hand;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function jsonSerialize()
    {
        return [
            'user_id' => $this->user->getKey(),
            'hand' => $this->hand,
        ];
    }

    public function setHandFromArray(array $handData): void
    {
        foreach ($handData as $cardData) {
            $this->addToHand(new CardState($cardData['suit'], $cardData['value']));
        }
    }
}
