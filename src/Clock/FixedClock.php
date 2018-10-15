<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Clock;

use Emonkak\DateTime\DateTime;

class FixedClock implements ClockInterface
{
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }
}
