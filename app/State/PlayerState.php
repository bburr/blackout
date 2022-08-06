<?php declare(strict_types=1);

namespace App\State;

use App\Models\User;
use App\State\Collections\CardCollection;

/**
 * @phpstan-consistent-constructor
 * @phpstan-import-type SerializedCardCollection from CardCollection
 * @phpstan-type SerializedPlayerState array{user_id: string, hand: SerializedCardCollection}
 */
class PlayerState extends AbstractState
{
    /**
     * @var CardCollection<int, CardState>
     */
    protected CardCollection $hand;

    public function __construct(protected User $user)
    {
        $this->hand = new CardCollection();
    }

    public function addToHand(CardState $cardState): void
    {
        $this->hand->add($cardState);
    }

    /**
     * @return CardCollection<int, CardState>
     */
    public function getHand(): CardCollection
    {
        return $this->hand;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @phpstan-return SerializedPlayerState
     */
    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->user->getKey(),
            'hand' => $this->hand->jsonSerialize(),
        ];
    }

    /**
     * @param array $playerData
     * @phpstan-param SerializedPlayerState $playerData
     * @return static
     */
    public static function loadFromSaveData(array $playerData): static
    {
        $user = (new User)->setAttribute('uuid', $playerData['user_id']);
        $player = new static($user);
        $player->setHandFromArray($playerData['hand']);

        return $player;
    }

    /**
     * @param array $handData
     * @phpstan-param SerializedCardCollection $handData
     * @return void
     */
    public function setHandFromArray(array $handData): void
    {
        foreach ($handData as $cardData) {
            $this->addToHand(new CardState($cardData['suit'], $cardData['value']));
        }
    }
}
