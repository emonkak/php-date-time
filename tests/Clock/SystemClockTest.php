<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests\Clock;

use Emonkak\DateTime\Clock\SystemClock;
use Emonkak\DateTime\Tests\AbstractTestCase;

class SystemClockTest extends AbstractTestCase
{
    public function testUtc(): void
    {
        $dateTime = SystemClock::utc()->getDateTime();
        $this->assertSame('UTC', $dateTime->getTimeZone()->getName());
    }

    public function testDefault(): void
    {
        $now = new \DateTimeImmutable();
        $dateTime = SystemClock::default()->getDateTime();
        $this->assertSame('UTC', $dateTime->getTimeZone()->getName());
        $this->greaterThanOrEqual($now, $dateTime);
    }

    public function testAnyTimeZone(): void
    {
        $now = new \DateTimeImmutable();
        $dateTime = (new SystemClock(new \DateTimeZone('+09:00')))->getDateTime();
        $this->assertSame('+09:00', $dateTime->getTimeZone()->getName());
        $this->greaterThanOrEqual($now, $dateTime);
    }
}
