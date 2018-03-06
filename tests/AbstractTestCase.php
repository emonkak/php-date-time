<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Duration;
use Emonkak\DateTime\Interval;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    protected function createDateTime($seconds, $nanos = 0): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('U.u', sprintf('%d.%06d', $seconds, intdiv($nanos, 1000)));
    }

    protected function assertDurationIs(int $s, int $n, Duration $duration): void
    {
        $this->compare([$s, $n], [
            (int) $duration->getSeconds(),
            (int) $duration->getNanos()
        ]);
    }

    protected function assertIntervalIs(int $s1, int $n1, int $s2, int $n2, Interval $interval): void
    {
        $this->compare([$s1, $n1, $s2, $n2], [
            (int) $interval->getStart()->format('U'),
            (int) $interval->getStart()->format('u') * 1000,
            (int) $interval->getEnd()->format('U'),
            (int) $interval->getEnd()->format('u') * 1000
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
