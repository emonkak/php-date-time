<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Clock;

use Emonkak\DateTime\DateTime;

/**
 * A clock providing access to the current instant, date and time using a time-zone.
 */
interface ClockInterface
{
    public function getDateTime(): DateTime;
}
