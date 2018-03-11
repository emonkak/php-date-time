<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests\Clock;

use Emonkak\DateTime\Clock\FixedClock;
use Emonkak\DateTime\DateTime;
use Emonkak\DateTime\Tests\AbstractTestCase;

class FixedClockTest extends AbstractTestCase
{
    public function testGetTime(): void
    {
        $now = new DateTime();
        $dateTime = (new FixedClock($now))->getDateTime();
        $this->assertSame($now, $dateTime);
    }
}
