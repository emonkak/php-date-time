<?php

declare(strict_types=1);

use Emonkak\DateTime\DateTime;

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

    public function getDateTime(): DateTime
    {
        return $this->baseClock->getDateTime()->plus($this->offset);
    }
}
