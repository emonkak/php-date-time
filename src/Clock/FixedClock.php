<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Clock;

class FixedClock implements ClockInterface
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }
}
