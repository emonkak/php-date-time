<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Duration;
use Emonkak\DateTime\Unit;

class DurationTest extends AbstractTestCase
{
    public function testZero(): void
    {
        $this->assertDurationIs(0, 0, Duration::zero());
    }

    public function testOfDays(): void
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 60 * 60 * 24, Duration::ofDays($i)->getSeconds());
        }
    }

    public function testOfHours(): void
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 3600, Duration::ofHours($i)->getSeconds());
        }
    }

    public function testOfMinutes(): void
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 60, Duration::ofMinutes($i)->getSeconds());
        }
    }

    /**
     * @dataProvider providerOfSeconds
     */
    public function testOfSeconds(int $seconds, int $nanoAdjustment, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $nanoAdjustment);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration);
    }

    public function providerOfSeconds(): array
    {
        return [
            [3, 1, 3, 1],
            [4, -999999, 3, 1],
            [2, 1000001, 3, 1],
            [-3, 1, -3, 1],
            [-4, 1000001, -3, 1],
            [-2, -999999, -3, 1],
            [1, -1000001, -1, 999999],
            [-1, -1000001, -3, 999999]
        ];
    }

    /**
     * @dataProvider providerOfMicros
     */
    public function testOfMicros(int $micros, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofMicros($micros);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration);
    }

    public function providerOfMicros(): array
    {
        return [
            [1, 0, 1],
            [999999, 0, 999999],
            [-999999, -1, 1],
            [1000001, 1, 1],
            [-1000001, -2, 999999],
        ];
    }

    /**
     * @dataProvider providerBetween
     */
    public function testBetween(int $seconds1, int $micros1, int $seconds2, int $micros2, int $seconds, int $micros): void
    {
        $d1 = $this->createDateTime($seconds1, $micros1);
        $d2 = $this->createDateTime($seconds2, $micros2);

        $this->assertDurationIs($seconds, $micros, Duration::between($d1, $d2));
    }

    public function providerBetween(): array
    {
        return [
            [0, 0, 0, 0, 0, 0],
            [3, 0, 7, 0, 4, 0],
            [7, 0, 3, 0, -4, 0],

            [0, 500000, 1, 500000, 1, 0],
            [0, 500000, 1, 750000, 1, 250000],
            [0, 500000, 1, 250000, 0, 750000],

            [-1, 500000, 0, 0, 0, 500000],
            [-1, 500000, 0, 500000, 1, 0],

            [0, 0, -1, 500000, -1, 500000],
            [0, 500000, -1, 500000, -1, 0],
        ];
    }

    public function testWithSeconds(): void
    {
        $duration = Duration::zero();
        $this->assertDurationIs(100, 0, $duration->withSeconds(100));
    }

    public function testWithMicros(): void
    {
        $duration = Duration::zero();
        $this->assertDurationIs(0, 1000, $duration->withMicros(1000));
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsZero(int $seconds, int $micros, int $ordering): void
    {
        $this->assertSame($ordering === 0, Duration::ofSeconds($seconds, $micros)->isZero());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsPositive(int $seconds, int $micros, int $ordering): void
    {
        $this->assertSame($ordering > 0, Duration::ofSeconds($seconds, $micros)->isPositive());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsPositiveOrZero(int $seconds, int $micros, int $ordering): void
    {
        $this->assertSame($ordering >= 0, Duration::ofSeconds($seconds, $micros)->isPositiveOrZero());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsNegative(int $seconds, int $micros, int $ordering): void
    {
        $this->assertSame($ordering < 0, Duration::ofSeconds($seconds, $micros)->isNegative());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsNegativeOrZero(int $seconds, int $micros, int $ordering): void
    {
        $this->assertSame($ordering <= 0, Duration::ofSeconds($seconds, $micros)->isNegativeOrZero());
    }

    public function providerCompareToZero(): array
    {
        return [
            [-1, -1, -1],
            [-1,  0, -1],
            [-1,  1, -1],
            [ 0, -1, -1],
            [ 0,  0,  0],
            [ 0,  1,  1],
            [ 1, -1,  1],
            [ 1,  0,  1],
            [ 1,  1,  1]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     */
    public function testCompareTo(int $seconds1, int $micros1, int $seconds2, int $micros2, int $expected): void
    {
        $duration1 = Duration::ofSeconds($seconds1, $micros1);
        $duration2 = Duration::ofSeconds($seconds2, $micros2);

        $this->assertSame($expected, $duration1->compareTo($duration2));
    }

    public function providerCompareTo(): array
    {
        return [
            [-1, -1, -1, -1, 0],
            [-1, -1, -1, 0, -1],
            [-1, -1, 0, -1, -1],
            [-1, -1, 0, 0, -1],
            [-1, -1, 0, 1, -1],
            [-1, -1, 1, 0, -1],
            [-1, -1, 1, 1, -1],
            [-1, 0, -1, -1, 1],
            [-1, 0, -1, 0, 0],
            [-1, 0, 0, -1, -1],
            [-1, 0, 0, 0, -1],
            [-1, 0, 0, 1, -1],
            [-1, 0, 1, 0, -1],
            [-1, 0, 1, 1, -1],
            [0, -1, -1, -1, 1],
            [0, -1, -1, 0, 1],
            [0, -1, 0, -1, 0],
            [0, -1, 0, 0, -1],
            [0, -1, 0, 1, -1],
            [0, -1, 1, 0, -1],
            [0, -1, 1, 1, -1],
            [0, 0, -1, -1, 1],
            [0, 0, -1, 0, 1],
            [0, 0, 0, -1, 1],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 1, -1],
            [0, 0, 1, 0, -1],
            [0, 0, 1, 1, -1],
            [0, 1, -1, -1, 1],
            [0, 1, -1, 0, 1],
            [0, 1, 0, -1, 1],
            [0, 1, 0, 0, 1],
            [0, 1, 0, 1, 0],
            [0, 1, 1, 0, -1],
            [0, 1, 1, 1, -1],
            [1, 0, -1, -1, 1],
            [1, 0, -1, 0, 1],
            [1, 0, 0, -1, 1],
            [1, 0, 0, 0, 1],
            [1, 0, 0, 1, 1],
            [1, 0, 1, 0, 0],
            [1, 0, 1, 1, -1],
            [1, 1, -1, -1, 1],
            [1, 1, -1, 0, 1],
            [1, 1, 0, -1, 1],
            [1, 1, 0, 0, 1],
            [1, 1, 0, 1, 1],
            [1, 1, 1, 0, 1],
            [1, 1, 1, 1, 0]
        ];
    }

    public function testComparisons()
    {
        $durations = [
            Duration::ofDays(-1),
            Duration::ofHours(-2),
            Duration::ofHours(-1),
            Duration::ofMinutes(-2),
            Duration::ofMinutes(-1),
            Duration::ofSeconds(-2),
            Duration::ofSeconds(-1),
            Duration::zero(),
            Duration::ofSeconds(1),
            Duration::ofSeconds(2),
            Duration::ofMinutes(1),
            Duration::ofMinutes(2),
            Duration::ofHours(1),
            Duration::ofHours(2),
            Duration::ofDays(1),
        ];

        $count = count($durations);

        for ($i = 0; $i < $count; $i++) {
            $a = $durations[$i];

            for ($j = 0; $j < $count; $j++) {
                $b = $durations[$j];

                if ($i < $j) {
                    $this->assertLessThan(0, $a->compareTo($b),        "$a < $b");
                    $this->assertTrue($a->isLessThan($b),              "$a < $b");
                    $this->assertTrue($a->isLessThanOrEqualTo($b),     "$a < $b");
                    $this->assertFalse($a->isGreaterThan($b),          "$a < $b");
                    $this->assertFalse($a->isGreaterThanOrEqualTo($b), "$a < $b");
                    $this->assertFalse($a->isEqualTo($b),              "$a < $b");
                } elseif ($i > $j) {
                    $this->assertGreaterThan(0, $a->compareTo($b),    "$a > $b");
                    $this->assertFalse($a->isLessThan($b),            "$a > $b");
                    $this->assertFalse($a->isLessThanOrEqualTo($b),   "$a > $b");
                    $this->assertTrue($a->isGreaterThan($b),          "$a > $b");
                    $this->assertTrue($a->isGreaterThanOrEqualTo($b), "$a > $b");
                    $this->assertFalse($a->isEqualTo($b),             "$a > $b");
                }

                if ($i === $j) {
                    $this->assertSame(0, $a->compareTo($b),           "$a == $b");
                    $this->assertFalse($a->isLessThan($b),            "$a == $b");
                    $this->assertTrue($a->isLessThanOrEqualTo($b),    "$a == $b");
                    $this->assertFalse($a->isGreaterThan($b),         "$a == $b");
                    $this->assertTrue($a->isGreaterThanOrEqualTo($b), "$a == $b");
                    $this->assertTrue($a->isEqualTo($b),              "$a == $b");
                }
            }
        }
    }

    /**
     * @dataProvider providerPlus
     */
    public function testPlus(int $s1, int $n1, int $s2, int $n2, int $s, int $n): void
    {
        $duration1 = Duration::ofSeconds($s1, $n1);
        $duration2 = Duration::ofSeconds($s2, $n2);

        $this->assertDurationIs($s, $n, $duration1->plus($duration2));
    }

    /**
     * @dataProvider providerPlus
     */
    public function testMinus(int $s1, int $n1, int $s2, int $n2, int $s, int $n): void
    {
        $duration1 = Duration::ofSeconds($s1, $n1);
        $duration2 = Duration::ofSeconds(-$s2, -$n2);

        $this->assertDurationIs($s, $n, $duration1->minus($duration2));
    }

    public function providerPlus(): array
    {
        return [
            [-1, -1, -1, -1, -3, 999998],
            [-1, -1, -1, 0, -3, 999999],
            [-1, -1, 0, -1, -2, 999998],
            [-1, -1, 0, 0, -2, 999999],
            [-1, -1, 0, 1, -1, 0],
            [-1, -1, 1, 0, -1, 999999],
            [-1, -1, 1, 1, 0, 0],
            [-1, 0, -1, -1, -3, 999999],
            [-1, 0, -1, 0, -2, 0],
            [-1, 0, 0, -1, -2, 999999],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -1, 1],
            [-1, 0, 1, 0, 0, 0],
            [-1, 0, 1, 1, 0, 1],
            [0, -1, -1, -1, -2, 999998],
            [0, -1, -1, 0, -2, 999999],
            [0, -1, 0, -1, -1, 999998],
            [0, -1, 0, 0, -1, 999999],
            [0, -1, 0, 1, 0, 0],
            [0, -1, 1, 0, 0, 999999],
            [0, -1, 1, 1, 1, 0],
            [0, 0, -1, -1, -2, 999999],
            [0, 0, -1, 0, -1, 0],
            [0, 0, 0, -1, -1, 999999],
            [0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 1],
            [0, 0, 1, 0, 1, 0],
            [0, 0, 1, 1, 1, 1],
            [0, 1, -1, -1, -1, 0],
            [0, 1, -1, 0, -1, 1],
            [0, 1, 0, -1, 0, 0],
            [0, 1, 0, 0, 0, 1],
            [0, 1, 0, 1, 0, 2],
            [0, 1, 1, 0, 1, 1],
            [0, 1, 1, 1, 1, 2],
            [1, 0, -1, -1, -1, 999999],
            [1, 0, -1, 0, 0, 0],
            [1, 0, 0, -1, 0, 999999],
            [1, 0, 0, 0, 1, 0],
            [1, 0, 0, 1, 1, 1],
            [1, 0, 1, 0, 2, 0],
            [1, 0, 1, 1, 2, 1],
            [1, 1, -1, -1, 0, 0],
            [1, 1, -1, 0, 0, 1],
            [1, 1, 0, -1, 1, 0],
            [1, 1, 0, 0, 1, 1],
            [1, 1, 0, 1, 1, 2],
            [1, 1, 1, 0, 2, 1],
            [1, 1, 1, 1, 2, 2],

            [1, 999999, 1, 1, 3, 0],
            [1, 999999, -1, -1, 0, 999998],
            [-1, -999999, 1, 999998, -1, 999999],
            [-1, -999999, -1, -1, -3, 0],
        ];
    }

    /**
     * @dataProvider providerPlusMicros
     */
    public function testPlusMicros(int $seconds, int $micros, int $microsToAdd, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $micros)->plusMicros($microsToAdd);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration);
    }

    public function providerPlusMicros(): array
    {
        return [
            [-1, 0, -1, -2, 999999],
            [-1, 0,  0, -1,      0],
            [-1, 0,  1, -1,      1],
            [-1, 0,  2, -1,      2],

            [0, 0, -1, -1, 999999],
            [0, 0,  0,  0,      0],
            [0, 0,  1,  0,      1],

            [1, 0, -2, 0, 999998],
            [1, 0, -1, 0, 999999],
            [1, 0,  0, 1,      0],
            [1, 0,  1, 1,      1],

            [-1, -5,  2, -2, 999997],
            [ 1,  5, -2,  1,      3],
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     */
    public function testPlusSeconds(int $seconds, int $micros, int $secondsToAdd, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $micros)->plusSeconds($secondsToAdd);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration);
    }

    public function providerPlusSeconds(): array
    {
        return [
            [-1, 0, -1, -2, 0],
            [-1, 0,  0, -1, 0],
            [-1, 0,  1,  0, 0],
            [-1, 0,  2,  1, 0],

            [0, 0, -1, -1, 0],
            [0, 0,  0,  0, 0],
            [0, 0,  1,  1, 0],

            [1, 0, -2, -1, 0],
            [1, 0, -1,  0, 0],
            [1, 0,  0,  1, 0],
            [1, 0,  1,  2, 0],

            [~PHP_INT_MAX, 0, PHP_INT_MAX, -1, 0],
            [PHP_INT_MAX, 0, ~PHP_INT_MAX, -1, 0],
            [PHP_INT_MAX, 0, 0, PHP_INT_MAX, 0],

            [-1, -5,  2, 0,  999995],
            [ 1,  5, -2, -1, 5],
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     */
    public function testPlusMinutes(int $seconds, int $minutesToAdd, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->plusMinutes($minutesToAdd);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerPlusMinutes(): array
    {
        return [
            [-1, -1, -61],
            [-1, 0, -1],
            [-1, 1, 59],
            [-1, 2, 119],

            [0, -1, -60],
            [0, 0, 0],
            [0, 1, 60],

            [1, -2, -119],
            [1, -1, -59],
            [1, 0, 1],
            [1, 1, 61]
        ];
    }

    /**
     * @dataProvider providerPlusHours
     */
    public function testPlusHours(int $seconds, int $hoursToAdd, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->plusHours($hoursToAdd);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerPlusHours(): array
    {
        return [
            [-1, -1, -3601],
            [-1, 0, -1],
            [-1, 1, 3599],
            [-1, 2, 7199],

            [0, -1, -3600],
            [0, 0, 0],
            [0, 1, 3600],

            [1, -2, -7199],
            [1, -1, -3599],
            [1, 0, 1],
            [1, 1, 3601]
        ];
    }

    /**
     * @dataProvider providerPlusDays
     */
    public function testPlusDays(int $seconds, int $daysToAdd, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->plusDays($daysToAdd);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerPlusDays(): array
    {
        return [
            [-1, -1, -86401],
            [-1, 0, -1],
            [-1, 1, 86399],
            [-1, 2, 172799],

            [0, -1, -86400],
            [0, 0, 0],
            [0, 1, 86400],

            [1, -2, -172799],
            [1, -1, -86399],
            [1, 0, 1],
            [1, 1, 86401]
        ];
    }

    /**
     * @dataProvider providerMinusMicros
     */
    public function testMinusMicros(int $seconds, int $micros, int $microsToSubtract, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $micros)->minusMicros($microsToSubtract);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration);
    }

    public function providerMinusMicros(): array
    {
        return [
            [-1, 0, -1, -1,      1],
            [-1, 0,  0, -1,      0],
            [-1, 0,  1, -2, 999999],
            [-1, 0,  2, -2, 999998],

            [0, 0, -1,  0,      1],
            [0, 0,  0,  0,      0],
            [0, 0,  1, -1, 999999],

            [1, 0, -2, 1,      2],
            [1, 0, -1, 1,      1],
            [1, 0,  0, 1,      0],
            [1, 0,  1, 0, 999999],

            [-1, -5,  2, -2, 999993],
            [ 1,  5, -2,  1,      7],
        ];
    }

    /**
     * @dataProvider providerMinusSeconds
     */
    public function testMinusSeconds(int $seconds, int $secondsToSubtract, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->minusSeconds($secondsToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerMinusSeconds(): array
    {
        return [
            [0, 0, 0],
            [0, 1, -1],
            [0, -1, 1],
            [0, PHP_INT_MAX, - PHP_INT_MAX],
            [0, ~PHP_INT_MAX + 1, PHP_INT_MAX],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [1, PHP_INT_MAX - 1, - PHP_INT_MAX + 2],
            [1, ~PHP_INT_MAX + 2, PHP_INT_MAX],
            [1, PHP_INT_MAX, - PHP_INT_MAX + 1],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
            [-1, PHP_INT_MAX, ~PHP_INT_MAX],
            [-1, ~PHP_INT_MAX + 1, PHP_INT_MAX - 1]
        ];
    }

    /**
     * @dataProvider providerMinusMinutes
     */
    public function testMinusMinutes(int $seconds, int $minutesToSubtract, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->minusMinutes($minutesToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerMinusMinutes(): array
    {
        return [
            [-1, -1, 59],
            [-1, 0, -1],
            [-1, 1, -61],
            [-1, 2, -121],

            [0, -1, 60],
            [0, 0, 0],
            [0, 1, -60],

            [1, -2, 121],
            [1, -1, 61],
            [1, 0, 1],
            [1, 1, -59]
        ];
    }

    /**
     * @dataProvider providerMinusHours
     */
    public function testMinusHours(int $seconds, int $hoursToSubtract, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->minusHours($hoursToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerMinusHours(): array
    {
        return [
            [-1, -1, 3599],
            [-1, 0, -1],
            [-1, 1, -3601],
            [-1, 2, -7201],

            [0, -1, 3600],
            [0, 0, 0],
            [0, 1, -3600],

            [1, -2, 7201],
            [1, -1, 3601],
            [1, 0, 1],
            [1, 1, -3599]
        ];
    }

    /**
     * @dataProvider providerMinusDays
     */
    public function testMinusDays(int $seconds, int $daysToSubtract, int $expectedSeconds): void
    {
        $duration = Duration::ofSeconds($seconds)->minusDays($daysToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    public function providerMinusDays(): array
    {
        return [
            [-1, -1, 86399],
            [-1, 0, -1],
            [-1, 1, -86401],
            [-1, 2, -172801],

            [0, -1, 86400],
            [0, 0, 0],
            [0, 1, -86400],

            [1, -2, 172801],
            [1, -1, 86401],
            [1, 0, 1],
            [1, 1, -86399]
        ];
    }

    /**
     * @dataProvider providerMultipliedBy
     */
    public function testMultipliedBy(int $second, int $nano, int $multiplicand, int $expectedSecond, int $expectedNano): void
    {
        $duration = Duration::ofSeconds($second, $nano);
        $duration = $duration->multipliedBy($multiplicand);

        $this->assertDurationIs($expectedSecond, $expectedNano, $duration);
    }

    public function providerMultipliedBy(): array
    {
        return [
            [-3, 0, -3, 9, 0],
            [-3, 1000, -3, 8, 997000],
            [-3, 999999, -3, 6, 000003],
            [-3, 0, -1, 3, 0],
            [-3, 1000, -1, 2, 999000],
            [-3, 999999, -1, 2, 000001],
            [-3, 0, 0, 0, 0],
            [-3, 1000, 0, 0, 0],
            [-3, 999999, 0, 0, 0],
            [-3, 0, 1, -3, 0],
            [-3, 1000, 1, -3, 1000],
            [-3, 999999, 1, -3, 999999],
            [-3, 0, 3, -9, 0],
            [-3, 1000, 3, -9, 3000],
            [-3, 999999, 3, -7, 999997],
            [-1, 0, -3, 3, 0],
            [-1, 1000, -3, 2, 997000],
            [-1, 999999, -3, 0, 3],
            [-1, 0, -1, 1, 0],
            [-1, 1000, -1, 0, 999000],
            [-1, 999999, -1, 0, 1],
            [-1, 0, 0, 0, 0],
            [-1, 1000, 0, 0, 0],
            [-1, 999999, 0, 0, 0],
            [-1, 0, 1, -1, 0],
            [-1, 1000, 1, -1, 1000],
            [-1, 999999, 1, -1, 999999],
            [-1, 0, 3, -3, 0],
            [-1, 1000, 3, -3, 3000],
            [-1, 999999, 3, -1, 999997],
            [0, 0, -3, 0, 0],
            [0, 1000, -3, -1, 997000],
            [0, 999999, -3, -3, 3],
            [0, 0, -1, 0, 0],
            [0, 1000, -1, -1, 999000],
            [0, 999999, -1, -1, 1],
            [0, 0, 0, 0, 0],
            [0, 1000, 0, 0, 0],
            [0, 999999, 0, 0, 0],
            [0, 0, 1, 0, 0],
            [0, 1000, 1, 0, 1000],
            [0, 999999, 1, 0, 999999],
            [0, 0, 3, 0, 0],
            [0, 1000, 3, 0, 3000],
            [0, 999999, 3, 2, 999997],
            [1, 0, -3, -3, 0],
            [1, 1000, -3, -4, 997000],
            [1, 999999, -3, -6, 3],
            [1, 0, -1, -1, 0],
            [1, 1000, -1, -2, 999000],
            [1, 999999, -1, -2, 1],
            [1, 0, 0, 0, 0],
            [1, 1000, 0, 0, 0],
            [1, 999999, 0, 0, 0],
            [1, 0, 1, 1, 0],
            [1, 1000, 1, 1, 1000],
            [1, 999999, 1, 1, 999999],
            [1, 0, 3, 3, 0],
            [1, 1000, 3, 3, 3000],
            [1, 999999, 3, 5, 999997],
            [3, 0, -3, -9, 0],
            [3, 1000, -3, -10, 997000],
            [3, 999999, -3, -12, 3],
            [3, 0, -1, -3, 0],
            [3, 1000, -1, -4, 999000],
            [3, 999999, -1, -4, 1],
            [3, 0, 0, 0, 0],
            [3, 1000, 0, 0, 0],
            [3, 999999, 0, 0, 0],
            [3, 0, 1, 3, 0],
            [3, 1000, 1, 3, 1000],
            [3, 999999, 1, 3, 999999],
            [3, 0, 3, 9, 0],
            [3, 1000, 3, 9, 3000],
            [3, 999999, 3, 11, 999997],
            [1, 0, PHP_INT_MAX, PHP_INT_MAX, 0],
            [1, 0, PHP_INT_MIN, PHP_INT_MIN, 0],
        ];
    }

    /**
     * @dataProvider providerDividedBy
     */
    public function testDividedBy(int $seconds, int $micros, int $divisor, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $micros)->dividedBy($divisor);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration);
    }

    public function providerDividedBy(): array
    {
        return [
            [3, 0, 1, 3, 0],
            [3, 0, 2, 1, 500000],
            [3, 0, 3, 1, 0],
            [3, 0, 4, 0, 750000],
            [3, 0, 5, 0, 600000],
            [3, 0, 6, 0, 500000],
            [3, 0, 7, 0, 428571],
            [3, 0, 8, 0, 375000],
            [0, 2, 2, 0, 1],
            [0, 1, 2, 0, 0],

            [3, 0, -1, -3, 0],
            [3, 0, -2, -2, 500000],
            [3, 0, -3, -1, 0],
            [3, 0, -4, -1, 250000],
            [3, 0, -5, -1, 400000],
            [3, 0, -6, -1, 500000],
            [3, 0, -7, -1, 571429],
            [3, 0, -8, -1, 625000],
            [0, 2, -2, -1, 999999],
            [0, 1, -2, 0, 0],

            [-3, 0, 1, -3, 0],
            [-3, 0, 2, -2, 500000],
            [-3, 0, 3, -1, 0],
            [-3, 0, 4, -1, 250000],
            [-3, 0, 5, -1, 400000],
            [-3, 0, 6, -1, 500000],
            [-3, 0, 7, -1, 571429],
            [-3, 0, 8, -1, 625000],
            [-1, 999998, 2, -1, 999999],
            [-1, 999999, 2, 0, 0],

            [-3, 0, -1, 3, 0],
            [-3, 0, -2, 1, 500000],
            [-3, 0, -3, 1, 0],
            [-3, 0, -4, 0, 750000],
            [-3, 0, -5, 0, 600000],
            [-3, 0, -6, 0, 500000],
            [-3, 0, -7, 0, 428571],
            [-3, 0, -8, 0, 375000],
            [-1, 999998, -2, 0, 1],
            [-1, 999999, -2, 0, 0],

            [10, 1, 7, 1, 428571],
            [10, 2, 7, 1, 428571],
            [10, 3, 7, 1, 428571],
            [10, 1, -7, -2, 571429],
            [10, 2, -7, -2, 571429],
            [10, 3, -7, -2, 571429],
        ];
    }

    /**
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testDividedByZeroThrowsException(): void
    {
        Duration::zero()->dividedBy(0);
    }

    /**
     * @dataProvider providerNegated
     */
    public function testNegated(int $seconds, int $micros, int $expectedSeconds, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $micros);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $duration->negated());
    }

    public function providerNegated(): array
    {
        return [
            [0, 0, 0, 0],
            [1, 0, -1, 0],
            [-1, 0, 1, 0],
            [1, 1, -2, 999999],
            [-2, 999999, 1, 1],
            [-1, 1, 0, 999999],
            [0, 999999, -1, 1]
        ];
    }

    public function testAbs(): void
    {
        for ($seconds = -3; $seconds <= 3; $seconds++) {
            $duration = Duration::ofSeconds($seconds)->abs();
            $this->assertDurationIs(\abs($seconds), 0, $duration);
        }
    }

    /**
     * @dataProvider providerTruncatedTo
     */
    public function testTruncatedTo(int $seconds, int $micros, string $unitValue, int $expectedSeconds, int $expectedMicros): void
    {
        $this->assertDurationIs(
            $expectedSeconds,
            $expectedMicros,
            Duration::ofSeconds($seconds, $micros)->truncatedTo(Unit::of($unitValue))
        );
    }

    public function providerTruncatedTo()
    {
        return [
            [86400 + 3600 + 60 + 1, 123456, Unit::MICRO,  86400 + 3600 + 60 + 1, 123456],
            [86400 + 3600 + 60 + 1, 123456, Unit::MILLI,  86400 + 3600 + 60 + 1, 123000],
            [86400 + 3600 + 60 + 1, 123456, Unit::SECOND, 86400 + 3600 + 60 + 1,      0],
            [86400 + 3600 + 60 + 1, 123456, Unit::MINUTE,     86400 + 3600 + 60,      0],
            [86400 + 3600 + 60 + 1, 123456, Unit::HOUR,            86400 + 3600,      0],
            [86400 + 3600 + 60 + 1, 123456, Unit::DAY,                    86400,      0],

            [-86400 - 3600 - 60 - 1, 123456, Unit::MINUTE,     -86400 - 3600 - 60,      0],
            [-86400 - 3600 - 60 - 1, 123456, Unit::MILLI,  -86400 - 3600 - 60 - 1, 124000],

            [86400 + 3600 + 60 + 1, 0, Unit::SECOND, 86400 + 3600 + 60 + 1, 0],
            [  -86400 - 3600 - 120, 0, Unit::MINUTE,   -86400 - 3600 - 120, 0],

            [-1,      0, Unit::SECOND, -1,      0],
            [-1, 123456, Unit::SECOND,  0,      0],
            [-1, 123456, Unit::MICRO,  -1, 123456],
            [ 0, 123456, Unit::SECOND,  0,      0],
            [ 0, 123456, Unit::MICRO,   0, 123456]
        ];
    }

    /**
     * @expectedException Emonkak\DateTime\DateTimeException
     * @dataProvider providerTruncatedThrowsException
     */
    public function testTruncatedThrowsException(string $unitValue): void
    {
        Duration::zero()->truncatedTo(Unit::of($unitValue));
    }

    public function providerTruncatedThrowsException(): array
    {
        return [
            [Unit::MONTH],
            [Unit::YEAR],
            [Unit::FOREVER],
        ];
    }

    public function testEquals(): void
    {
        $test5a = Duration::ofSeconds(5);
        $test5b = Duration::ofSeconds(5);
        $test6a = Duration::ofSeconds(6);
        $test6b = Duration::ofSeconds(6);

        $this->assertTrue($test5a->isEqualTo($test5a));
        $this->assertTrue($test5a->isEqualTo($test5b));
        $this->assertFalse($test5a->isEqualTo($test6a));
        $this->assertFalse($test5a->isEqualTo($test6b));

        $this->assertTrue($test5b->isEqualTo($test5a));
        $this->assertTrue($test5b->isEqualTo($test5b));
        $this->assertFalse($test5b->isEqualTo($test6a));
        $this->assertFalse($test5b->isEqualTo($test6b));

        $this->assertFalse($test6a->isEqualTo($test5a));
        $this->assertFalse($test6a->isEqualTo($test5b));
        $this->assertTrue($test6a->isEqualTo($test6a));
        $this->assertTrue($test6a->isEqualTo($test6b));

        $this->assertFalse($test6b->isEqualTo($test5a));
        $this->assertFalse($test6b->isEqualTo($test5b));
        $this->assertTrue($test6b->isEqualTo($test6a));
        $this->assertTrue($test6b->isEqualTo($test6b));
    }

    /**
     * @dataProvider providerToMicros
     */
    public function testToMicros(int $seconds, int $micros, int $expectedMicros)
    {
        $duration = Duration::ofSeconds($seconds, $micros);
        $this->assertSame($expectedMicros, $duration->toMicros());
    }

    public function providerToMicros() : array
    {
        return [
            [-2, 000001, -1999999],
            [-2, 999999, -1000001],
            [ 1, 000001,  1000001],
            [ 1, 999999,  1999999]
        ];
    }

    /**
     * @dataProvider providerToMinutes
     */
    public function testGetMinutes(int $seconds, int $expectedMinutes): void
    {
        $duration = Duration::ofSeconds($seconds);
        $this->assertSame($expectedMinutes, $duration->toMinutes());
    }

    public function providerToMinutes(): array
    {
        return [
            [-121, -2],
            [ -60, -1],
            [ -59,  0],
            [   0,  0],
            [  59,  0],
            [  60,  1],
            [ 121,  2]
        ];
    }

    /**
     * @dataProvider providerToHours
     */
    public function testToHours(int $seconds, int $expectedHours): void
    {
        $duration = Duration::ofSeconds($seconds);
        $this->assertSame($expectedHours, $duration->toHours());
    }

    public function providerToHours(): array
    {
        return [
            [-7201, -2],
            [-3600, -1],
            [-3599,  0],
            [    0,  0],
            [ 3599,  0],
            [ 3600,  1],
            [ 7201,  2]
        ];
    }

    /**
     * @dataProvider providerToDays
     */
    public function testToDays(int $seconds, int $expectedDays): void
    {
        $duration = Duration::ofSeconds($seconds);
        $this->assertSame($expectedDays, $duration->toDays());
    }

    public function providerToDays(): array
    {
        return [
            [-172800, -2],
            [ -86400, -1],
            [ -86399,  0],
            [      0,  0],
            [  86399,  0],
            [  86400,  1],
            [ 172800,  2]
        ];
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString(int $seconds, int $micros, string $expected): void
    {
        $this->assertSame($expected, (string) Duration::ofSeconds($seconds, $micros));
    }

    public function providerToString(): array
    {
        return [
            [0, 0, 'PT0S'],
            [0, 1, 'PT0.000001S'],
            [1, 0, 'PT1S'],
            [1, 1, 'PT1.000001S'],
            [60, 0, 'PT1M'],
            [60, 1, 'PT1M0.000001S'],
            [61, 0, 'PT1M1S'],
            [61, 1, 'PT1M1.000001S'],
            [3600, 0, 'PT1H'],
            [3600, 1, 'PT1H0.000001S'],
            [3601, 0, 'PT1H1S'],
            [3601, 1, 'PT1H1.000001S'],
            [3660, 0, 'PT1H1M'],
            [3660, 1, 'PT1H1M0.000001S'],
            [3661, 0, 'PT1H1M1S'],
            [3661, 1, 'PT1H1M1.000001S'],
            [86400, 0, 'PT24H'],
            [86400, 1, 'PT24H0.000001S'],
            [90000, 0, 'PT25H'],
            [90000, 1, 'PT25H0.000001S'],
            [90001, 0, 'PT25H1S'],
            [90001, 1, 'PT25H1.000001S'],
            [90060, 0, 'PT25H1M'],
            [90060, 1, 'PT25H1M0.000001S'],
            [90061, 0, 'PT25H1M1S'],
            [90061, 1, 'PT25H1M1.000001S'],

            [-1, 0, 'PT-1S'],
            [-1, 1, 'PT-0.999999S'],
            [-60, 0, 'PT-1M'],
            [-60, 1, 'PT-59.999999S'],
            [-61, 0, 'PT-1M-1S'],
            [-61, 1, 'PT-1M-0.999999S'],
            [-62, 0, 'PT-1M-2S'],
            [-62, 1, 'PT-1M-1.999999S'],
            [-3600, 0, 'PT-1H'],
            [-3600, 1, 'PT-59M-59.999999S'],
            [-3601, 0, 'PT-1H-1S'],
            [-3601, 1, 'PT-1H-0.999999S'],
            [-3602, 0, 'PT-1H-2S'],
            [-3602, 1, 'PT-1H-1.999999S'],
            [-3660, 0, 'PT-1H-1M'],
            [-3660, 1, 'PT-1H-59.999999S'],
            [-3661, 0, 'PT-1H-1M-1S'],
            [-3661, 1, 'PT-1H-1M-0.999999S'],
            [-3662, 0, 'PT-1H-1M-2S'],
            [-3662, 1, 'PT-1H-1M-1.999999S'],
            [-86400, 0, 'PT-24H'],
            [-86400, 1, 'PT-23H-59M-59.999999S'],
            [-86401, 0, 'PT-24H-1S'],
            [-86401, 1, 'PT-24H-0.999999S'],
            [-90000, 0, 'PT-25H'],
            [-90000, 1, 'PT-24H-59M-59.999999S'],
            [-90001, 0, 'PT-25H-1S'],
            [-90001, 1, 'PT-25H-0.999999S'],
            [-90060, 0, 'PT-25H-1M'],
            [-90060, 1, 'PT-25H-59.999999S'],
            [-90061, 0, 'PT-25H-1M-1S'],
            [-90061, 1, 'PT-25H-1M-0.999999S'],
        ];
    }
}
