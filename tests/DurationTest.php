<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Duration;

class DurationTest extends AbstractTestCase
{
    public function testZero(): void
    {
        $this->assertDurationIs(0, 0, Duration::zero());
    }

    /**
     * @dataProvider providerOfSeconds
     */
    public function testOfSeconds(int $seconds, int $nanoAdjustment, int $expectedSeconds, int $expectedNanos): void
    {
        $duration = Duration::ofSeconds($seconds, $nanoAdjustment);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration);
    }

    public function providerOfSeconds(): array
    {
        return [
            [3, 1, 3, 1],
            [4, -999999999, 3, 1],
            [2, 1000000001, 3, 1],
            [-3, 1, -3, 1],
            [-4, 1000000001, -3, 1],
            [-2, -999999999, -3, 1],
            [1, -1000000001, -1, 999999999],
            [-1, -1000000001, -3, 999999999]
        ];
    }

    public function testOfMinutes(): void
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 60, Duration::ofMinutes($i)->getSeconds());
        }
    }

    public function testOfHours(): void
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 3600, Duration::ofHours($i)->getSeconds());
        }
    }

    public function testOfDays(): void
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 60 * 60 * 24, Duration::ofDays($i)->getSeconds());
        }
    }

    /**
     * @dataProvider providerBetween
     */
    public function testBetween(int $seconds1, int $nanos1, int $seconds2, int $nanos2, int $seconds, int $nanos): void
    {
        $d1 = $this->createDateTime($seconds1, $nanos1);
        $d2 = $this->createDateTime($seconds2, $nanos2);

        $this->assertDurationIs($seconds, $nanos, Duration::between($d1, $d2));
    }

    public function providerBetween(): array
    {
        return [
            [0, 0, 0, 0, 0, 0],
            [3, 0, 7, 0, 4, 0],
            [7, 0, 3, 0, -4, 0],

            [0, 500000000, 1, 500000000, 1, 0],
            [0, 500000000, 1, 750000000, 1, 250000000],
            [0, 500000000, 1, 250000000, 0, 750000000],

            [-1, 500000000, 0, 0, 0, 500000000],
            [-1, 500000000, 0, 500000000, 1, 0],

            [0, 0, -1, 500000000, -1, 500000000],
            [0, 500000000, -1, 500000000, -1, 0],
        ];
    }

    public function testWithSeconds(): void
    {
        $duration = Duration::zero();
        $this->assertDurationIs(100, 0, $duration->withSeconds(100));
    }

    public function testWithNanos(): void
    {
        $duration = Duration::zero();
        $this->assertDurationIs(0, 1000, $duration->withNanos(1000));
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsZero(int $seconds, int $nanos, int $ordering): void
    {
        $this->assertSame($ordering === 0, Duration::ofSeconds($seconds, $nanos)->isZero());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsPositive(int $seconds, int $nanos, int $ordering): void
    {
        $this->assertSame($ordering > 0, Duration::ofSeconds($seconds, $nanos)->isPositive());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsPositiveOrZero(int $seconds, int $nanos, int $ordering): void
    {
        $this->assertSame($ordering >= 0, Duration::ofSeconds($seconds, $nanos)->isPositiveOrZero());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsNegative(int $seconds, int $nanos, int $ordering): void
    {
        $this->assertSame($ordering < 0, Duration::ofSeconds($seconds, $nanos)->isNegative());
    }

    /**
     * @dataProvider providerCompareToZero
     */
    public function testIsNegativeOrZero(int $seconds, int $nanos, int $ordering): void
    {
        $this->assertSame($ordering <= 0, Duration::ofSeconds($seconds, $nanos)->isNegativeOrZero());
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
    public function testCompareTo(int $seconds1, int $nanos1, int $seconds2, int $nanos2, int $expected): void
    {
        $duration1 = Duration::ofSeconds($seconds1, $nanos1);
        $duration2 = Duration::ofSeconds($seconds2, $nanos2);

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
            [-1, -1, -1, -1, -3, 999999998],
            [-1, -1, -1, 0, -3, 999999999],
            [-1, -1, 0, -1, -2, 999999998],
            [-1, -1, 0, 0, -2, 999999999],
            [-1, -1, 0, 1, -1, 0],
            [-1, -1, 1, 0, -1, 999999999],
            [-1, -1, 1, 1, 0, 0],
            [-1, 0, -1, -1, -3, 999999999],
            [-1, 0, -1, 0, -2, 0],
            [-1, 0, 0, -1, -2, 999999999],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -1, 1],
            [-1, 0, 1, 0, 0, 0],
            [-1, 0, 1, 1, 0, 1],
            [0, -1, -1, -1, -2, 999999998],
            [0, -1, -1, 0, -2, 999999999],
            [0, -1, 0, -1, -1, 999999998],
            [0, -1, 0, 0, -1, 999999999],
            [0, -1, 0, 1, 0, 0],
            [0, -1, 1, 0, 0, 999999999],
            [0, -1, 1, 1, 1, 0],
            [0, 0, -1, -1, -2, 999999999],
            [0, 0, -1, 0, -1, 0],
            [0, 0, 0, -1, -1, 999999999],
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
            [1, 0, -1, -1, -1, 999999999],
            [1, 0, -1, 0, 0, 0],
            [1, 0, 0, -1, 0, 999999999],
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

            [1, 999999999, 1, 1, 3, 0],
            [1, 999999999, -1, -1, 0, 999999998],
            [-1, -999999999, 1, 999999998, -1, 999999999],
            [-1, -999999999, -1, -1, -3, 0],
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     */
    public function testPlusSeconds(int $seconds, int $nanos, int $secondsToAdd, int $expectedSeconds, int $expectedNanos): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos)->plusSeconds($secondsToAdd);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration);
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

            [~\PHP_INT_MAX, 0, \PHP_INT_MAX, -1, 0],
            [\PHP_INT_MAX, 0, ~\PHP_INT_MAX, -1, 0],
            [\PHP_INT_MAX, 0, 0, \PHP_INT_MAX, 0],

            [-1, -5,  2, 0,  999999995],
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
            [0, \PHP_INT_MAX, - \PHP_INT_MAX],
            [0, ~\PHP_INT_MAX + 1, \PHP_INT_MAX],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [1, \PHP_INT_MAX - 1, - \PHP_INT_MAX + 2],
            [1, ~\PHP_INT_MAX + 2, \PHP_INT_MAX],
            [1, \PHP_INT_MAX, - \PHP_INT_MAX + 1],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
            [-1, \PHP_INT_MAX, ~\PHP_INT_MAX],
            [-1, ~\PHP_INT_MAX + 1, \PHP_INT_MAX - 1]
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
            [-3, 1000, -3, 8, 999997000],
            [-3, 999999999, -3, 6, 000000003],
            [-3, 0, -1, 3, 0],
            [-3, 1000, -1, 2, 999999000],
            [-3, 999999999, -1, 2, 000000001],
            [-3, 0, 0, 0, 0],
            [-3, 1000, 0, 0, 0],
            [-3, 999999999, 0, 0, 0],
            [-3, 0, 1, -3, 0],
            [-3, 1000, 1, -3, 1000],
            [-3, 999999999, 1, -3, 999999999],
            [-3, 0, 3, -9, 0],
            [-3, 1000, 3, -9, 3000],
            [-3, 999999999, 3, -7, 999999997],
            [-1, 0, -3, 3, 0],
            [-1, 1000, -3, 2, 999997000],
            [-1, 999999999, -3, 0, 3],
            [-1, 0, -1, 1, 0],
            [-1, 1000, -1, 0, 999999000],
            [-1, 999999999, -1, 0, 1],
            [-1, 0, 0, 0, 0],
            [-1, 1000, 0, 0, 0],
            [-1, 999999999, 0, 0, 0],
            [-1, 0, 1, -1, 0],
            [-1, 1000, 1, -1, 1000],
            [-1, 999999999, 1, -1, 999999999],
            [-1, 0, 3, -3, 0],
            [-1, 1000, 3, -3, 3000],
            [-1, 999999999, 3, -1, 999999997],
            [0, 0, -3, 0, 0],
            [0, 1000, -3, -1, 999997000],
            [0, 999999999, -3, -3, 3],
            [0, 0, -1, 0, 0],
            [0, 1000, -1, -1, 999999000],
            [0, 999999999, -1, -1, 1],
            [0, 0, 0, 0, 0],
            [0, 1000, 0, 0, 0],
            [0, 999999999, 0, 0, 0],
            [0, 0, 1, 0, 0],
            [0, 1000, 1, 0, 1000],
            [0, 999999999, 1, 0, 999999999],
            [0, 0, 3, 0, 0],
            [0, 1000, 3, 0, 3000],
            [0, 999999999, 3, 2, 999999997],
            [1, 0, -3, -3, 0],
            [1, 1000, -3, -4, 999997000],
            [1, 999999999, -3, -6, 3],
            [1, 0, -1, -1, 0],
            [1, 1000, -1, -2, 999999000],
            [1, 999999999, -1, -2, 1],
            [1, 0, 0, 0, 0],
            [1, 1000, 0, 0, 0],
            [1, 999999999, 0, 0, 0],
            [1, 0, 1, 1, 0],
            [1, 1000, 1, 1, 1000],
            [1, 999999999, 1, 1, 999999999],
            [1, 0, 3, 3, 0],
            [1, 1000, 3, 3, 3000],
            [1, 999999999, 3, 5, 999999997],
            [3, 0, -3, -9, 0],
            [3, 1000, -3, -10, 999997000],
            [3, 999999999, -3, -12, 3],
            [3, 0, -1, -3, 0],
            [3, 1000, -1, -4, 999999000],
            [3, 999999999, -1, -4, 1],
            [3, 0, 0, 0, 0],
            [3, 1000, 0, 0, 0],
            [3, 999999999, 0, 0, 0],
            [3, 0, 1, 3, 0],
            [3, 1000, 1, 3, 1000],
            [3, 999999999, 1, 3, 999999999],
            [3, 0, 3, 9, 0],
            [3, 1000, 3, 9, 3000],
            [3, 999999999, 3, 11, 999999997],
            [1, 0,  \PHP_INT_MAX, \PHP_INT_MAX, 0],
            [1, 0, \PHP_INT_MIN, \PHP_INT_MIN, 0],
        ];
    }

    /**
     * @dataProvider providerDividedBy
     */
    public function testDividedBy(int $seconds, int $nanos, int $divisor, int $expectedSeconds, int $expectedNanos): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos)->dividedBy($divisor);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration);
    }

    public function providerDividedBy(): array
    {
        return [
            [3, 0, 1, 3, 0],
            [3, 0, 2, 1, 500000000],
            [3, 0, 3, 1, 0],
            [3, 0, 4, 0, 750000000],
            [3, 0, 5, 0, 600000000],
            [3, 0, 6, 0, 500000000],
            [3, 0, 7, 0, 428571428],
            [3, 0, 8, 0, 375000000],
            [0, 2, 2, 0, 1],
            [0, 1, 2, 0, 0],

            [3, 0, -1, -3, 0],
            [3, 0, -2, -2, 500000000],
            [3, 0, -3, -1, 0],
            [3, 0, -4, -1, 250000000],
            [3, 0, -5, -1, 400000000],
            [3, 0, -6, -1, 500000000],
            [3, 0, -7, -1, 571428572],
            [3, 0, -8, -1, 625000000],
            [0, 2, -2, -1, 999999999],
            [0, 1, -2, 0, 0],

            [-3, 0, 1, -3, 0],
            [-3, 0, 2, -2, 500000000],
            [-3, 0, 3, -1, 0],
            [-3, 0, 4, -1, 250000000],
            [-3, 0, 5, -1, 400000000],
            [-3, 0, 6, -1, 500000000],
            [-3, 0, 7, -1, 571428572],
            [-3, 0, 8, -1, 625000000],
            [-1, 999999998, 2, -1, 999999999],
            [-1, 999999999, 2, 0, 0],

            [-3, 0, -1, 3, 0],
            [-3, 0, -2, 1, 500000000],
            [-3, 0, -3, 1, 0],
            [-3, 0, -4, 0, 750000000],
            [-3, 0, -5, 0, 600000000],
            [-3, 0, -6, 0, 500000000],
            [-3, 0, -7, 0, 428571428],
            [-3, 0, -8, 0, 375000000],
            [-1, 999999998, -2, 0, 1],
            [-1, 999999999, -2, 0, 0],

            [10, 1, 7, 1, 428571428],
            [10, 2, 7, 1, 428571428],
            [10, 3, 7, 1, 428571429],
            [10, 1, -7, -2, 571428572],
            [10, 2, -7, -2, 571428572],
            [10, 3, -7, -2, 571428571],
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
    public function testNegated(int $seconds, int $nanos, int $expectedSeconds, int $expectedNanos): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration->negated());
    }

    public function providerNegated(): array
    {
        return [
            [0, 0, 0, 0],
            [1, 0, -1, 0],
            [-1, 0, 1, 0],
            [1, 1, -2, 999999999],
            [-2, 999999999, 1, 1],
            [-1, 1, 0, 999999999],
            [0, 999999999, -1, 1]
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
    public function testTruncatedTo($seconds, $nanos, $unitSeconds, $unitNanos, $expectedSeconds, $expectedNanos): void
    {
        $this->assertDurationIs(
            $expectedSeconds,
            $expectedNanos,
            Duration::ofSeconds($seconds, $nanos)->truncatedTo(Duration::ofSeconds($unitSeconds, $unitNanos))
        );
    }

    public function providerTruncatedTo()
    {
        return [
            [86400 + 3600 + 60 + 1, 123456789, 0,                            1,       86400 + 3600 + 60 + 1, 123456789],
            [86400 + 3600 + 60 + 1, 123456789, 0,                            1000,    86400 + 3600 + 60 + 1, 123456000],
            [86400 + 3600 + 60 + 1, 123456789, 0,                            1000000, 86400 + 3600 + 60 + 1, 123000000],
            [86400 + 3600 + 60 + 1, 123456789, 1,                            0,       86400 + 3600 + 60 + 1, 0],
            [86400 + 3600 + 60 + 1, 123456789, Duration::SECONDS_PER_MINUTE, 0,       86400 + 3600 + 60,     0],
            [86400 + 3600 + 60 + 1, 123456789, Duration::SECONDS_PER_HOUR,   0,       86400 + 3600,          0],
            [86400 + 3600 + 60 + 1, 123456789, Duration::SECONDS_PER_DAY,    0,       86400,                 0],

            [86400 + 3600 + 60 + 1, 123456789,  Duration::SECONDS_PER_MINUTE * 90, 0, 86400 + 0,     0],
            [86400 + 7200 + 60 + 1, 123456789,  Duration::SECONDS_PER_MINUTE * 90, 0, 86400 + 5400,  0],
            [86400 + 10800 + 60 + 1, 123456789, Duration::SECONDS_PER_MINUTE * 90, 0, 86400 + 10800, 0],

            [-86400 - 3600 - 60 - 1, 123456789, Duration::SECONDS_PER_MINUTE, 0,    -86400 - 3600 - 60,     0],
            [-86400 - 3600 - 60 - 1, 123456789, 0,                            1000, -86400 - 3600 - 60 - 1, 123457000],

            [86400 + 3600 + 60 + 1, 0, 1,                            0, 86400 + 3600 + 60 + 1, 0],
            [-86400 - 3600 - 120,   0, Duration::SECONDS_PER_MINUTE, 0, -86400 - 3600 - 120,   0],

            [-1, 0,         1, 0, -1, 0],
            [-1, 123456789, 1, 0, 0,  0],
            [-1, 123456789, 0, 1, -1, 123456789],
            [0, 123456789,  1, 0, 0,  0],
            [0, 123456789,  0, 1, 0,  123456789]
        ];
    }

    /**
     * @expectedException Emonkak\DateTime\DateTimeException
     * @dataProvider providerTruncatedThrowsException
     */
    public function testTruncatedThrowsException(int $seconds, int $nanos): void
    {
        Duration::zero()->truncatedTo(Duration::ofSeconds($seconds, $nanos));
    }

    public function providerTruncatedThrowsException(): array
    {
        return [
            [60 * 60 * 24 + 1, 0],
            [60 * 60 * 24, 1]
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
     * @dataProvider providerToTotalMillis
     */
    public function testToTotalMillis(int $seconds, int $nanos, int $expectedMillis): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertSame($expectedMillis, $duration->ToTotalMillis());
    }

    public function providerToTotalMillis(): array
    {
        return [
            [-123, 456000001, -122544],
            [-123, 456999999, -122544],
            [ 123, 456000001,  123456],
            [ 123, 456999999,  123456]
        ];
    }

    /**
     * @dataProvider providerToTotalMicros
     */
    public function testToTotalMicros(int $seconds, int $nanos, int $expectedMicros): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertSame($expectedMicros, $duration->toTotalMicros());
    }

    public function providerToTotalMicros(): array
    {
        return [
            [-123, 456789001, -122543211],
            [-123, 456789999, -122543211],
            [ 123, 456789001,  123456789],
            [ 123, 456789999,  123456789]
        ];
    }

    /**
     * @dataProvider providerToTotalNanos
     */
    public function testToTotalNanos(int $seconds, int $nanos, int $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertSame($expectedNanos, $duration->toTotalNanos());
    }

    public function providerToTotalNanos() : array
    {
        return [
            [-2, 000000001, -1999999999],
            [-2, 999999999, -1000000001],
            [ 1, 000000001,  1000000001],
            [ 1, 999999999,  1999999999]
        ];
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString(int $seconds, int $nanos, string $expected): void
    {
        $this->assertSame($expected, (string) Duration::ofSeconds($seconds, $nanos));
    }

    public function providerToString(): array
    {
        return [
            [0, 0, 'PT0S'],
            [0, 1, 'PT0.000000001S'],
            [1, 0, 'PT1S'],
            [1, 1, 'PT1.000000001S'],
            [60, 0, 'PT1M'],
            [60, 1, 'PT1M0.000000001S'],
            [61, 0, 'PT1M1S'],
            [61, 1, 'PT1M1.000000001S'],
            [3600, 0, 'PT1H'],
            [3600, 1, 'PT1H0.000000001S'],
            [3601, 0, 'PT1H1S'],
            [3601, 1, 'PT1H1.000000001S'],
            [3660, 0, 'PT1H1M'],
            [3660, 1, 'PT1H1M0.000000001S'],
            [3661, 0, 'PT1H1M1S'],
            [3661, 1, 'PT1H1M1.000000001S'],
            [86400, 0, 'PT24H'],
            [86400, 1, 'PT24H0.000000001S'],
            [90000, 0, 'PT25H'],
            [90000, 1, 'PT25H0.000000001S'],
            [90001, 0, 'PT25H1S'],
            [90001, 1, 'PT25H1.000000001S'],
            [90060, 0, 'PT25H1M'],
            [90060, 1, 'PT25H1M0.000000001S'],
            [90061, 0, 'PT25H1M1S'],
            [90061, 1, 'PT25H1M1.000000001S'],

            [-1, 0, 'PT-1S'],
            [-1, 1, 'PT-0.999999999S'],
            [-60, 0, 'PT-1M'],
            [-60, 1, 'PT-59.999999999S'],
            [-61, 0, 'PT-1M-1S'],
            [-61, 1, 'PT-1M-0.999999999S'],
            [-62, 0, 'PT-1M-2S'],
            [-62, 1, 'PT-1M-1.999999999S'],
            [-3600, 0, 'PT-1H'],
            [-3600, 1, 'PT-59M-59.999999999S'],
            [-3601, 0, 'PT-1H-1S'],
            [-3601, 1, 'PT-1H-0.999999999S'],
            [-3602, 0, 'PT-1H-2S'],
            [-3602, 1, 'PT-1H-1.999999999S'],
            [-3660, 0, 'PT-1H-1M'],
            [-3660, 1, 'PT-1H-59.999999999S'],
            [-3661, 0, 'PT-1H-1M-1S'],
            [-3661, 1, 'PT-1H-1M-0.999999999S'],
            [-3662, 0, 'PT-1H-1M-2S'],
            [-3662, 1, 'PT-1H-1M-1.999999999S'],
            [-86400, 0, 'PT-24H'],
            [-86400, 1, 'PT-23H-59M-59.999999999S'],
            [-86401, 0, 'PT-24H-1S'],
            [-86401, 1, 'PT-24H-0.999999999S'],
            [-90000, 0, 'PT-25H'],
            [-90000, 1, 'PT-24H-59M-59.999999999S'],
            [-90001, 0, 'PT-25H-1S'],
            [-90001, 1, 'PT-25H-0.999999999S'],
            [-90060, 0, 'PT-25H-1M'],
            [-90060, 1, 'PT-25H-59.999999999S'],
            [-90061, 0, 'PT-25H-1M-1S'],
            [-90061, 1, 'PT-25H-1M-0.999999999S'],
        ];
    }
}
