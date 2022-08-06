<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\Exceptions\InvalidBetAmountException;
use App\State\CardState;
use App\State\Collections\CardCollection;
use App\State\RoundState;
use Tests\TestCase;

class RoundStateTest extends TestCase
{
    public function testConstructor(): void
    {
        $roundNumber = 1;
        $numCards = 3;
        $isNumCardsAscending = true;
        $nextPlayerIndex = 0;

        $round = new RoundState($roundNumber, $numCards, $isNumCardsAscending, $nextPlayerIndex, $nextPlayerIndex);
        $this->assertInstanceOf(RoundState::class, $round);
        $this->assertInstanceOf(CardCollection::class, $round->getPlays());
        $this->assertEquals(0, $round->getPlays()->count());
        $this->assertCount(0, $round->getBets());
        $this->assertNull($round->getTrumpCard());
        $this->assertEquals($roundNumber, $round->getRoundNumber());
        $this->assertEquals($numCards, $round->getNumCards());
        $this->assertEquals($isNumCardsAscending, $round->isNumCardsAscending());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToBet());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToPlay());
    }

    public function testLoadFromSaveDataEmpty(): void
    {
        $roundNumber = 1;
        $numCards = 3;
        $isNumCardsAscending = true;
        $nextPlayerIndex = 0;

        $round = RoundState::loadFromSaveData([
            'config' => [
                'round_number' => $roundNumber,
                'num_cards' => $numCards,
                'is_num_cards_ascending' => $isNumCardsAscending,
                'next_player_index_to_bet' => $nextPlayerIndex,
                'next_player_index_to_play' => $nextPlayerIndex,
            ],
            'trump_card' => null,
            'bets' => [],
            'plays' => [],
        ]);

        $this->assertInstanceOf(RoundState::class, $round);
        $this->assertInstanceOf(CardCollection::class, $round->getPlays());
        $this->assertEquals(0, $round->getPlays()->count());
        $this->assertCount(0, $round->getBets());
        $this->assertNull($round->getTrumpCard());
        $this->assertEquals($roundNumber, $round->getRoundNumber());
        $this->assertEquals($numCards, $round->getNumCards());
        $this->assertEquals($isNumCardsAscending, $round->isNumCardsAscending());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToBet());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToPlay());
    }

    public function testLoadFromSaveData(): void
    {
        $roundNumber = 1;
        $numCards = 3;
        $isNumCardsAscending = true;
        $nextPlayerIndex = 1;

        $round = RoundState::loadFromSaveData([
            'config' => [
                'round_number' => $roundNumber,
                'num_cards' => $numCards,
                'is_num_cards_ascending' => $isNumCardsAscending,
                'next_player_index_to_bet' => $nextPlayerIndex,
                'next_player_index_to_play' => $nextPlayerIndex,
            ],
            'trump_card' => ['suit' => 'S', 'value' => 12],
            'bets' => [
                0 => 1,
            ],
            'plays' => [
                0 => ['suit' => 'H', 'value' => 10],
            ],
        ]);

        $this->assertInstanceOf(RoundState::class, $round);
        $this->assertInstanceOf(CardCollection::class, $round->getPlays());
        $this->assertEquals(1, $round->getPlays()->count());
        $this->assertEquals('Q of Hearts', (string) $round->getPlays()[0]);
        $this->assertCount(1, $round->getBets());
        $this->assertEquals(1, $round->getBets()[0]);
        $this->assertEquals('A of Spades', (string) $round->getTrumpCard());
        $this->assertEquals($roundNumber, $round->getRoundNumber());
        $this->assertEquals($numCards, $round->getNumCards());
        $this->assertEquals($isNumCardsAscending, $round->isNumCardsAscending());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToBet());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToPlay());
    }

    public function dataMakeBetForNextPlayer(): array
    {
        return [
            [-1, 3, true],
            [0, 3, false],
            [1, 3, false],
            [2, 3, false],
            [3, 3, false],
            [4, 3, true],
        ];
    }

    /**
     * @dataProvider dataMakeBetForNextPlayer
     * @param int $bet
     * @param int $numCards
     * @param bool $expectException
     * @return void
     */
    public function testMakeBetForNextPlayer(int $bet, int $numCards, bool $expectException): void
    {
        $round = new RoundState(1, $numCards, false, 0, 0);

        if ($expectException) {
            $this->expectException(InvalidBetAmountException::class);
        }

        $round->makeBetForNextPlayer($bet);

        $this->assertCount($expectException ? 0 : 1, $round->getBets());
    }

    public function testMakePlayForNextPlayer(): void
    {
        $round = new RoundState(1, 3, false, 0, 0);

        $round->makePlayForNextPlayer(new CardState('S', 12));

        $this->assertCount(1, $round->getPlays());
    }
}
