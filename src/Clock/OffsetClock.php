<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Clock;

use Emonkak\DateTime\DateTime;
use Emonkak\DateTime\Duration;

class OffsetClock implements ClockInterface
{
    /**
     * @var ClockInterface
     */
    private $baseClock;

    /**
     * @var Duration
     */
    private $offset;

    public function __construct(ClockInterface $baseClock, Duration $offset)
    {
        $this->baseClock = $baseClock;
        $this->offset = $offset;
    }

    public function getDateTime(): \DateTimeInterface
    {
        return DateTime::from($this->baseClock->getDateTime())
            ->plusDuration($this->offset);
    }
}
