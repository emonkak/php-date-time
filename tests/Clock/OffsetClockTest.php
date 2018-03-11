<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests\Clock;

use Emonkak\DateTime\Clock\ClockInterface;
use Emonkak\DateTime\Clock\OffsetClock;
use Emonkak\DateTime\DateTime;
use Emonkak\DateTime\Duration;
use Emonkak\DateTime\Tests\AbstractTestCase;

class OffsetClockTest extends AbstractTestCase
{
    public function testGetTime(): void
    {
        $dateTime = new DateTime('2001-02-03T04:05:06.123456Z');
        $offsetDuration = Duration::ofSeconds(123);

        $clock = $this->createMock(ClockInterface::class);
        $clock
            ->expects($this->once())
            ->method('getDateTime')
            ->willReturn($dateTime);

        $offsetDateTime = (new OffsetClock($clock, $offsetDuration))->getDateTime();

        $this->assertSame('2001-02-03T04:07:09.123456Z', (string) $offsetDateTime);
    }
}
