<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

interface UnitInterface
{
    public function addTo(\DateTimeInterface $dateTime, int $amount): DateTime;

    public function between(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): int;

    public function getDuration(): Duration;

    public function __toString(): string;
}
