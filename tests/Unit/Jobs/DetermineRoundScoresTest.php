<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\DetermineRoundScores;
use App\State\CardState;
use App\State\Collections\TrickCollection;
use App\State\GameSettings;
use App\State\RoundState;
use Tests\TestCase;

/**
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-import-type SerializedTrickCollection from TrickCollection
 */
class DetermineRoundScoresTest extends TestCase
{
    /**
     * @phpstan-return array<string, array{0: int[], 1: SerializedCardState|null, 2: SerializedTrickCollection, 3: int[]}>
     */
    public function dataHandle(): array
    {
        return [
            'All blackout' => [
                [1, 1, 0],
                ['suit' => 'S', 'value' => 12],
                [
                    ['leading_card' => null, 'trick_winner_index' => 0, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 0, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 2, 'plays' => []],
                ],
                [0, 0, 0],
            ],
            'One correct' => [
                [1, 0, 0],
                ['suit' => 'S', 'value' => 12],
                [
                    ['leading_card' => null, 'trick_winner_index' => 0, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 0, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 2, 'plays' => []],
                ],
                [0, 10, 0],
            ],
            'Two correct' => [
                [1, 0, 0],
                ['suit' => 'S', 'value' => 12],
                [
                    ['leading_card' => null, 'trick_winner_index' => 0, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 2, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 2, 'plays' => []],
                ],
                [11, 10, 0],
            ],
            'All correct' => [
                [1, 0, 2],
                ['suit' => 'S', 'value' => 12],
                [
                    ['leading_card' => null, 'trick_winner_index' => 0, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 2, 'plays' => []],
                    ['leading_card' => null, 'trick_winner_index' => 2, 'plays' => []],
                ],
                [11, 10, 12],
            ],
        ];
    }

    /**
     * @dataProvider dataHandle
     * @param int[] $betsData
     * @param array|null $trumpCardData
     * @phpstan-param SerializedCardState|null $trumpCardData
     * @param array $trickData
     * @phpstan-param SerializedTrickCollection $trickData
     * @param int[] $expectedScores
     * @return void
     */
    public function testHandle(array $betsData, ?array $trumpCardData, array $trickData, array $expectedScores): void
    {
        /** @var RoundState $roundState */
        $roundState = $this->partialMock(RoundState::class);

        if (isset($trumpCardData)) {
            $roundState->setTrumpCard(new CardState($trumpCardData['suit'], $trumpCardData['value']));
        }

        $roundState->setBets($betsData);
        $roundState->setPreviousTricksFromArray($trickData);

        $subject = new DetermineRoundScores($roundState, new GameSettings(count($betsData), [
            'points_for_correct_bet' => 10,
        ]));

        $this->assertEquals($expectedScores, $subject->handle());
    }
}
