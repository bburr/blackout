<?php declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\DetermineDealer;
use Tests\TestCase;

class DetermineDealerTest extends TestCase
{
    public function testHandle(): void
    {
        $playerIndexes = [1, 2, 3, 4, 5];

        $subject = new DetermineDealer($playerIndexes);

        // todo any way to force the numbers to test a recursive call?
        $this->assertContains($subject->handle(), $playerIndexes);
    }
}
