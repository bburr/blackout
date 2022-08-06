<?php declare(strict_types=1);

namespace Tests\Unit\State\Collections;

use App\State\Collections\CardCollection;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CardCollectionTest extends TestCase
{
    public function testConstructor()
    {
        $collection = new CardCollection();

        $this->assertInstanceOf(Collection::class, $collection);
    }
}
