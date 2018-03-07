<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Duration;
use Emonkak\DateTime\Interval;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    protected function createDateTime($seconds, $micros = 0): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('U.u', sprintf('%d.%06d', $seconds, $micros));
    }

    protected function assertDurationIs(int $s, int $n, Duration $duration): void
    {
        $this->compare([$s, $n], [
            (int) $duration->getSeconds(),
            (int) $duration->getMicros()
        ]);
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
