<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\DetermineTrickWinner;
use App\State\CardState;
use App\State\Collections\CardCollection;
use Tests\TestCase;

/**
 * @phpstan-import-type SerializedCardState from CardState
 * @phpstan-import-type SerializedCardCollection from CardCollection
 */
class DetermineTrickWinnerTest extends TestCase
{
    /**
     * @phpstan-return array<string, array{0: SerializedCardState|null, 1: SerializedCardState, 2: SerializedCardCollection, 3: int}>
     */
    public function dataHandle(): array
    {
        return [
            'All same as leading, leading takes' => [
                ['suit' => 'C', 'value' => 2],
                ['suit' => 'H', 'value' => 10],
                [
                    ['suit' => 'H', 'value' => 10],
                    ['suit' => 'H', 'value' => 5],
                    ['suit' => 'H', 'value' => 2],
                ],
                0,
            ],
            'All same as leading, second takes' => [
                ['suit' => 'C', 'value' => 2],
                ['suit' => 'H', 'value' => 10],
                [
                    ['suit' => 'H', 'value' => 10],
                    ['suit' => 'H', 'value' => 12],
                    ['suit' => 'H', 'value' => 2],
                ],
                1,
            ],
            'All different from leading, leading takes' => [
                ['suit' => 'C', 'value' => 2],
                ['suit' => 'H', 'value' => 10],
                [
                    ['suit' => 'H', 'value' => 10],
                    ['suit' => 'D', 'value' => 12],
                    ['suit' => 'S', 'value' => 2],
                ],
                0,
            ],
            'All same as leading, last takes' => [
                ['suit' => 'C', 'value' => 2],
                ['suit' => 'H', 'value' => 10],
                [
                    ['suit' => 'H', 'value' => 10],
                    ['suit' => 'H', 'value' => 2],
                    ['suit' => 'H', 'value' => 12],
                ],
                2,
            ],
            'Trump played, second takes' => [
                ['suit' => 'C', 'value' => 2],
                ['suit' => 'H', 'value' => 10],
                [
                    ['suit' => 'H', 'value' => 10],
                    ['suit' => 'C', 'value' => 2],
                    ['suit' => 'H', 'value' => 12],
                ],
                1,
            ],
            'Trump played, leading takes' => [
                ['suit' => 'C', 'value' => 2],
                ['suit' => 'C', 'value' => 10],
                [
                    ['suit' => 'C', 'value' => 10],
                    ['suit' => 'C', 'value' => 2],
                    ['suit' => 'H', 'value' => 12],
                ],
                0,
            ],
        ];
    }

    /**
     * @dataProvider dataHandle
     * @param array|null $trumpCardData
     * @phpstan-param SerializedCardState|null $trumpCardData
     * @param array $leadingCardData
     * @phpstan-param SerializedCardState $leadingCardData
     * @param array $playsData
     * @phpstan-param SerializedCardCollection $playsData
     * @param int $expectedWinnerIndex
     * @return void
     */
    public function testHandle(?array $trumpCardData, array $leadingCardData, array $playsData, int $expectedWinnerIndex): void
    {
        $trumpCard = null;

        if (isset($trumpCardData)) {
            $trumpCard = new CardState($trumpCardData['suit'], $trumpCardData['value']);
        }

        $leadingCard = new CardState($leadingCardData['suit'], $leadingCardData['value']);

        $plays = new CardCollection();

        foreach ($playsData as $playData) {
            $plays->add(new CardState($playData['suit'], $playData['value']));
        }

        $subject = new DetermineTrickWinner($trumpCard, $leadingCard, $plays);

        $this->assertEquals($expectedWinnerIndex, $subject->handle());
    }
}
