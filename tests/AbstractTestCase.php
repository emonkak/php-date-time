<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\DayOfWeek;
use Emonkak\DateTime\Duration;
use Emonkak\DateTime\Interval;
use Emonkak\DateTime\UnitInterface;
use Emonkak\DateTime\FieldInterface;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    protected function assertDateTimeIs(int $year, int $month, int $day, int $hour, int $minute, int $second, int $micro, string $timeZone, \DateTimeInterface $dateTime): void
    {
        $this->compare([$year, $month, $day, $hour, $minute, $second, $micro, $timeZone], [
            (int) $dateTime->format('Y'),
            (int) $dateTime->format('n'),
            (int) $dateTime->format('j'),
            (int) $dateTime->format('G'),
            (int) $dateTime->format('i'),
            (int) $dateTime->format('s'),
            (int) $dateTime->format('u'),
            $dateTime->format('P')
        ]);
    }

    protected function assertDayOfWeekIs(int $dayOfWeekValue, DayOfWeek $dayOfWeek): void
    {
        $this->assertSame($dayOfWeekValue, $dayOfWeek->getValue());
    }

    protected function assertDurationIs(int $seconds, int $micros, Duration $duration): void
    {
        $this->compare([$seconds, $micros], [
            $duration->getSeconds(),
            $duration->getMicros()
        ]);
    }

    protected function assertFieldIs(string $name, FieldInterface $field): void
    {
        $this->assertSame($name, (string) $field);
    }

    protected function assertIntervalIs(int $seconds1, int $micros1, int $seconds2, int $micros2, Interval $interval): void
    {
        $this->compare([$seconds1, $micros1, $seconds2, $micros2], [
            (int) $interval->getStart()->format('U'),
            (int) $interval->getStart()->format('u'),
            (int) $interval->getEnd()->format('U'),
            (int) $interval->getEnd()->format('u')
        ]);
    }

    protected function assertUnitIs(string $name, UnitInterface $unit): void
    {
        $this->assertSame($name, (string) $unit);
    }

    private function compare(array $expected, array $actual): void
    {
        $message = $this->export($actual) . ' !== ' . $this->export($expected);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $actual[$key], $message);
        }
    }

    private function export(array $values): string
    {
        foreach ($values as & $value) {
            $value = var_export($value, true);
        }

        return '(' . implode(', ', $values) . ')';
    }
}
