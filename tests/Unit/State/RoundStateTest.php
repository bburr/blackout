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
        $numTricks = 3;
        $isNumTricksAscending = true;
        $nextPlayerIndex = 0;

        $round = new RoundState($roundNumber, $numTricks, $isNumTricksAscending, $nextPlayerIndex, $nextPlayerIndex);
        $this->assertInstanceOf(RoundState::class, $round);
        $this->assertInstanceOf(CardCollection::class, $round->getCurrentTrick()->getPlays());
        $this->assertEquals(0, $round->getCurrentTrick()->getPlays()->count());
        $this->assertCount(0, $round->getBets());
        $this->assertNull($round->getTrumpCard());
        $this->assertNull($round->getCurrentTrick()->getLeadingCard());
        $this->assertEquals($roundNumber, $round->getRoundNumber());
        $this->assertEquals($numTricks, $round->getNumTricks());
        $this->assertEquals($isNumTricksAscending, $round->isNumTricksAscending());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToBet());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToPlay());
    }

    public function testLoadFromSaveDataEmpty(): void
    {
        $roundNumber = 1;
        $numTricks = 3;
        $isNumTricksAscending = true;
        $nextPlayerIndex = 0;

        $round = RoundState::loadFromSaveData([
            'config' => [
                'round_number' => $roundNumber,
                'num_tricks' => $numTricks,
                'is_num_tricks_ascending' => $isNumTricksAscending,
                'next_player_index_to_bet' => $nextPlayerIndex,
                'next_player_index_to_play' => $nextPlayerIndex,
            ],
            'trump_card' => null,
            'bets' => [],
            'current_trick' => [
                'leading_card' => null,
                'plays' => [],
            ],
            'previous_tricks' => [],
        ]);

        $this->assertInstanceOf(RoundState::class, $round);
        $this->assertInstanceOf(CardCollection::class, $round->getCurrentTrick()->getPlays());
        $this->assertEquals(0, $round->getCurrentTrick()->getPlays()->count());
        $this->assertCount(0, $round->getBets());
        $this->assertNull($round->getTrumpCard());
        $this->assertNull($round->getCurrentTrick()->getLeadingCard());
        $this->assertEquals($roundNumber, $round->getRoundNumber());
        $this->assertEquals($numTricks, $round->getNumTricks());
        $this->assertEquals($isNumTricksAscending, $round->isNumTricksAscending());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToBet());
        $this->assertEquals($nextPlayerIndex, $round->getNextPlayerIndexToPlay());
    }

    public function testLoadFromSaveData(): void
    {
        $roundNumber = 1;
        $numTricks = 3;
        $isNumTricksAscending = true;
        $nextPlayerIndex = 1;

        $round = RoundState::loadFromSaveData([
            'config' => [
                'round_number' => $roundNumber,
                'num_tricks' => $numTricks,
                'is_num_tricks_ascending' => $isNumTricksAscending,
                'next_player_index_to_bet' => $nextPlayerIndex,
                'next_player_index_to_play' => $nextPlayerIndex,
            ],
            'trump_card' => ['suit' => 'S', 'value' => 12],
            'bets' => [
                0 => 1,
            ],
            'previous_tricks' => [],
            'current_trick' => [
                'leading_card' => ['suit' => 'H', 'value' => 10],
                'plays' => [
                    0 => ['suit' => 'H', 'value' => 10],
                ],
            ],
        ]);

        $this->assertInstanceOf(RoundState::class, $round);
        $this->assertInstanceOf(CardCollection::class, $round->getCurrentTrick()->getPlays());
        $this->assertEquals(1, $round->getCurrentTrick()->getPlays()->count());
        $this->assertEquals('Q of Hearts', (string) $round->getCurrentTrick()->getPlays()[0]);
        $this->assertCount(1, $round->getBets());
        $this->assertEquals(1, $round->getBets()[0]);
        $this->assertEquals('A of Spades', (string) $round->getTrumpCard());
        $this->assertEquals('Q of Hearts', (string) $round->getCurrentTrick()->getLeadingCard());
        $this->assertEquals($roundNumber, $round->getRoundNumber());
        $this->assertEquals($numTricks, $round->getNumTricks());
        $this->assertEquals($isNumTricksAscending, $round->isNumTricksAscending());
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
     * @param int $numTricks
     * @param bool $expectException
     * @return void
     */
    public function testMakeBetForNextPlayer(int $bet, int $numTricks, bool $expectException): void
    {
        $round = new RoundState(1, $numTricks, false, 0, 0);

        if ($expectException) {
            $this->expectException(InvalidBetAmountException::class);
        }

        $round->makeBetForNextPlayer($bet);

        $this->assertCount($expectException ? 0 : 1, $round->getBets());
    }

    public function testMakePlayForNextPlayer(): void
    {
        $round = new RoundState(1, 3, false, 0, 0);

        $this->assertNull($round->getCurrentTrick()->getLeadingCard());

        $cardState = new CardState('S', 12);
        $round->makePlayForNextPlayer($cardState);

        $this->assertEquals((string) $cardState, (string) $round->getCurrentTrick()->getLeadingCard());

        $round->makePlayForNextPlayer(new CardState('H', 10));

        $this->assertEquals((string) $cardState, (string) $round->getCurrentTrick()->getLeadingCard());

        $this->assertCount(1, $round->getCurrentTrick()->getPlays());
    }
}
