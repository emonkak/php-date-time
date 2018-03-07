<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Clock;

use Emonkak\DateTime\DateTime;

class SystemClock implements ClockInterface
{
    /**
     * @var \DateTimeZone
     */
    private $timeZone;

    public static function utc(): SystemClock
    {
        return new SystemClock(new \DateTimeZone('UTC'));
    }

    public static function default(): SystemClock
    {
        return new SystemClock(new \DateTimeZone(date_default_timezone_get()));
    }

    public function __construct(\DateTimeZone $timeZone)
    {
        $this->timeZone = $timeZone;
    }

    public function getDateTime(): DateTime
    {
        return new DateTime('now', $this->timeZone);
    }
}
