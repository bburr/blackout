<?php declare(strict_types=1);

namespace Tests\Unit\State;

use App\Exceptions\CardShoeIsEmptyException;
use App\State\CardShoeState;
use App\State\CardState;
use App\State\Collections\CardCollection;
use Tests\TestCase;

class CardShoeStateTest extends TestCase
{
    const NUM_CARDS_PER_DECK = 52;

    /**
     * @var array<string, int>
     */
    protected array $uniqueCards = [];

    /**
     * @return array<int, array<int, int|null>>
     */
    public function dataConstructor(): array
    {
        return [
            [null],
            [1],
            [2],
            [3],
        ];
    }

    /**
     * @dataProvider dataConstructor
     * @param int|null $numDecks
     * @return void
     */
    public function testConstructor(?int $numDecks): void
    {
        $cardShoe = new CardShoeState($numDecks);

        if ($numDecks === null) {
            try {
                $this->getPropertyValueFromObject($cardShoe, 'cards');
            }
            catch (\Throwable $e) {
                $this->assertEquals('Typed property App\State\CardShoeState::$cards must not be accessed before initialization', $e->getMessage());
            }

            return;
        }

        /** @var CardCollection $cards */
        $cards = $this->getPropertyValueFromObject($cardShoe, 'cards');
        $this->assertInstanceOf(CardCollection::class, $cards);
        $this->assertEquals($numDecks * self::NUM_CARDS_PER_DECK, $cards->count());

        /** @var CardState $card */
        foreach ($cards as $card) {
            $this->addCardForUniquenessCheck($card);
        }

        $this->checkCardUniqueness($numDecks);
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function dataDealCardOut(): array
    {
        return [
            [1],
            [2],
            [3],
        ];
    }

    /**
     * @dataProvider dataDealCardOut
     * @param int $numDecks
     * @return void
     * @throws CardShoeIsEmptyException
     */
    public function testDealCardOut(int $numDecks): void
    {
        $cardShoe = new CardShoeState($numDecks);

        for ($i = 0; $i < self::NUM_CARDS_PER_DECK * $numDecks; $i++) {
            $card = $cardShoe->dealCardOut();
            $this->addCardForUniquenessCheck($card);
        }

        $this->checkCardUniqueness($numDecks);

        $this->expectException(CardShoeIsEmptyException::class);

        $cardShoe->dealCardOut();
    }

    protected function addCardForUniquenessCheck(CardState $card): void
    {
        $this->assertInstanceOf(CardState::class, $card);
        $cardString = (string) $card;
        $this->uniqueCards[$cardString] = isset($this->uniqueCards[$cardString]) ? $this->uniqueCards[$cardString] + 1 : 1;
    }

    protected function checkCardUniqueness(int $numDecks): void
    {
        foreach ($this->uniqueCards as $amount) {
            $this->assertEquals($numDecks, $amount);
        }

        $this->assertCount(self::NUM_CARDS_PER_DECK, $this->uniqueCards);
    }

    public function testLoadFromSaveDataEmpty(): void
    {
        $cardShoe = CardShoeState::loadFromSaveData(['cards' => []]);

        $this->expectException(CardShoeIsEmptyException::class);

        $cardShoe->dealCardOut();
    }

    /**
     * @return array<int, array<int, int>>
     */
    public function dataLoadFromSaveData(): array
    {
        return [
            [1],
            [2],
            [3],
        ];
    }

    /**
     * @dataProvider dataLoadFromSaveData
     * @param int $numDecks
     * @return void
     * @covers CardShoeState::setCardsFromArray()
     * @throws CardShoeIsEmptyException
     */
    public function testLoadFromSaveData(int $numDecks): void
    {
        $cardShoe = new CardShoeState($numDecks);

        $saveData = $cardShoe->jsonSerialize();

        $loadedCardShoe = CardShoeState::loadFromSaveData($saveData);

        /** @var CardCollection $cards */
        $cards = $this->getPropertyValueFromObject($loadedCardShoe, 'cards');
        $this->assertInstanceOf(CardCollection::class, $cards);
        $this->assertEquals($numDecks * self::NUM_CARDS_PER_DECK, $cards->count());

        /** @var CardState $card */
        foreach ($cards as $card) {
            $this->addCardForUniquenessCheck($card);
        }

        $this->checkCardUniqueness($numDecks);

        for ($i = 0; $i < $numDecks * self::NUM_CARDS_PER_DECK; $i++) {
            $card1 = $cardShoe->dealCardOut();
            $card2 = $loadedCardShoe->dealCardOut();

            $this->assertEquals((string) $card1, (string) $card2);
        }
    }
}
