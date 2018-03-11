<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Unit;
use Emonkak\DateTime\DateTime;

/**
 * @covers Emonkak\DateTime\Unit
 */
class UnitTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     * @runInSeparateProcess
     */
    public function testOf(string $name, int $expectedSeconds, int $expectedMicros): void
    {
        $unit = Unit::of($name);
        $this->assertUnitIs($name, $unit);
        $this->assertDurationIs($expectedSeconds, $expectedMicros, $unit->getDuration());
    }

    public function providerOf(): array
    {
        return [
            [Unit::MICRO, 0, 1],
            [Unit::MILLI, 0, 1000],
            [Unit::SECOND, 1, 0],
            [Unit::MINUTE, 60, 0],
            [Unit::HOUR, 60 * 60, 0],
            [Unit::DAY, 60 * 60 * 24, 0],
            [Unit::WEEK, 60 * 60 * 24 * 7, 0],
            [Unit::MONTH, 31556952 / 12, 0],
            [Unit::YEAR, 31556952, 0],
            [Unit::FOREVER, PHP_INT_MAX, 999999]
        ];
    }

    /**
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testOfInvalidNameThrowsException(): void
    {
        Unit::of('invalid');
    }

    public function testMicro(): void
    {
        $this->assertUnitIs(Unit::MICRO, Unit::micro());
    }

    public function testMilli(): void
    {
        $this->assertUnitIs(Unit::MILLI, Unit::milli());
    }

    public function testSecond(): void
    {
        $this->assertUnitIs(Unit::SECOND, Unit::second());
    }

    public function testMinute(): void
    {
        $this->assertUnitIs(Unit::MINUTE, Unit::minute());
    }

    public function testHour(): void
    {
        $this->assertUnitIs(Unit::HOUR, Unit::hour());
    }

    public function testDay(): void
    {
        $this->assertUnitIs(Unit::DAY, Unit::day());
    }

    public function testWeek(): void
    {
        $this->assertUnitIs(Unit::WEEK, Unit::week());
    }

    public function testMonth(): void
    {
        $this->assertUnitIs(Unit::MONTH, Unit::month());
    }

    public function testYear(): void
    {
        $this->assertUnitIs(Unit::YEAR, Unit::year());
    }

    public function testForever(): void
    {
        $this->assertSame(Unit::of(Unit::FOREVER), Unit::forever());
    }

    /**
     * @dataProvider providerAddTo
     */
    public function testAddTo(string $unit, int $amount, string $expectedDateTime): void
    {
        $dateTime = new DateTime('2001-02-03T04:05:06.123456Z');
        $unit = Unit::of($unit);
        $this->assertSame($expectedDateTime, (string) $unit->addTo($dateTime, $amount));
    }

    public function providerAddTo(): array
    {
        return [
            [Unit::MICRO, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::MICRO, 1, '2001-02-03T04:05:06.123457Z'],
            [Unit::MICRO, -1, '2001-02-03T04:05:06.123455Z'],
            [Unit::MILLI, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::MILLI, 1, '2001-02-03T04:05:06.124456Z'],
            [Unit::MILLI, -1, '2001-02-03T04:05:06.122456Z'],
            [Unit::SECOND, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::SECOND, 1, '2001-02-03T04:05:07.123456Z'],
            [Unit::SECOND, -1, '2001-02-03T04:05:05.123456Z'],
            [Unit::MINUTE, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::MINUTE, 1, '2001-02-03T04:06:06.123456Z'],
            [Unit::MINUTE, -1, '2001-02-03T04:04:06.123456Z'],
            [Unit::HOUR, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::HOUR, 1, '2001-02-03T05:05:06.123456Z'],
            [Unit::HOUR, -1, '2001-02-03T03:05:06.123456Z'],
            [Unit::DAY, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::DAY, 1, '2001-02-04T04:05:06.123456Z'],
            [Unit::DAY, -1, '2001-02-02T04:05:06.123456Z'],
            [Unit::WEEK, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::WEEK, 1, '2001-02-10T04:05:06.123456Z'],
            [Unit::WEEK, -1, '2001-01-27T04:05:06.123456Z'],
            [Unit::MONTH, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::MONTH, 1, '2001-03-03T04:05:06.123456Z'],
            [Unit::MONTH, -1, '2001-01-03T04:05:06.123456Z'],
            [Unit::YEAR, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::YEAR, 1, '2002-02-03T04:05:06.123456Z'],
            [Unit::YEAR, -1, '2000-02-03T04:05:06.123456Z'],
            [Unit::FOREVER, 0, '2001-02-03T04:05:06.123456Z'],
            [Unit::FOREVER, 1, '9999-12-31T23:59:59.999999Z'],
            [Unit::FOREVER, -1, '-9999-01-01T00:00:00Z']
        ];
    }

    /**
     * @dataProvider providerBetween
     */
    public function testBetween(string $startInclusiveString, string $endExclusiveString, string $unit, int $expectedAmount): void
    {
        $startInclusive = new \DateTimeImmutable($startInclusiveString);
        $endExclusive = new \DateTimeImmutable($endExclusiveString);
        $this->assertSame($expectedAmount, Unit::of($unit)->between($startInclusive, $endExclusive));
        $this->assertSame(-$expectedAmount, Unit::of($unit)->between($endExclusive, $startInclusive));
    }

    public function providerBetween(): array
    {
        return [
            // date only
            ['2000-01-01T00:00:00Z', '2000-01-01T00:00:00Z', Unit::DAY, 0],
            ['2000-01-01T00:00:00Z', '2000-01-01T00:00:00Z', Unit::WEEK, 0],
            ['2000-01-01T00:00:00Z', '2000-01-01T00:00:00Z', Unit::MONTH, 0],
            ['2000-01-01T00:00:00Z', '2000-01-01T00:00:00Z', Unit::YEAR, 0],

            ['2000-01-15T00:00:00Z', '2000-02-14T00:00:00Z', Unit::DAY, 30],
            ['2000-01-15T00:00:00Z', '2000-02-15T00:00:00Z', Unit::DAY, 31],
            ['2000-01-15T00:00:00Z', '2000-02-16T00:00:00Z', Unit::DAY, 32],

            ['2000-01-15T00:00:00Z', '2000-02-17T00:00:00Z', Unit::WEEK, 4],
            ['2000-01-15T00:00:00Z', '2000-02-18T00:00:00Z', Unit::WEEK, 4],
            ['2000-01-15T00:00:00Z', '2000-02-19T00:00:00Z', Unit::WEEK, 5],
            ['2000-01-15T00:00:00Z', '2000-02-20T00:00:00Z', Unit::WEEK, 5],

            ['2000-01-15T00:00:00Z', '2000-02-14T00:00:00Z', Unit::MONTH, 0],
            ['2000-01-15T00:00:00Z', '2000-02-15T00:00:00Z', Unit::MONTH, 1],
            ['2000-01-15T00:00:00Z', '2000-02-16T00:00:00Z', Unit::MONTH, 1],
            ['2000-01-15T00:00:00Z', '2000-03-14T00:00:00Z', Unit::MONTH, 1],
            ['2000-01-15T00:00:00Z', '2000-03-15T00:00:00Z', Unit::MONTH, 2],
            ['2000-01-15T00:00:00Z', '2000-03-16T00:00:00Z', Unit::MONTH, 2],

            ['2000-01-15T00:00:00Z', '2001-01-14T00:00:00Z', Unit::YEAR, 0],
            ['2000-01-15T00:00:00Z', '2001-01-15T00:00:00Z', Unit::YEAR, 1],
            ['2000-01-15T00:00:00Z', '2001-01-16T00:00:00Z', Unit::YEAR, 1],
            ['2000-01-15T00:00:00Z', '2004-01-14T00:00:00Z', Unit::YEAR, 3],
            ['2000-01-15T00:00:00Z', '2004-01-15T00:00:00Z', Unit::YEAR, 4],
            ['2000-01-15T00:00:00Z', '2004-01-16T00:00:00Z', Unit::YEAR, 4],

            ['2000-01-15T00:00:00Z', '2001-01-14T00:00:00Z', Unit::FOREVER, 0],
            ['2000-01-15T00:00:00Z', '2001-01-15T00:00:00Z', Unit::FOREVER, 0],
            ['2000-01-15T00:00:00Z', '2001-01-16T00:00:00Z', Unit::FOREVER, 0],
            ['2000-01-15T00:00:00Z', '2004-01-14T00:00:00Z', Unit::FOREVER, 0],
            ['2000-01-15T00:00:00Z', '2004-01-15T00:00:00Z', Unit::FOREVER, 0],
            ['2000-01-15T00:00:00Z', '2004-01-16T00:00:00Z', Unit::FOREVER, 0],

            // time only
            ['1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', Unit::MICRO, 0],
            ['1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', Unit::MILLI, 0],
            ['1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', Unit::SECOND, 0],
            ['1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', Unit::MINUTE, 0],
            ['1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', Unit::HOUR, 0],

            ['1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', Unit::MICRO, 2 * 3600 * 1000000],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', Unit::MILLI, 2 * 3600 * 1000],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', Unit::SECOND, 2 * 3600],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', Unit::MINUTE, 2 * 60],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', Unit::HOUR, 2],

            ['1970-01-01T00:00:00Z', '1970-01-01T14:00:00Z', Unit::MICRO, 14 * 3600 * 1000000],
            ['1970-01-01T00:00:00Z', '1970-01-01T14:00:00Z', Unit::MILLI, 14 * 3600 * 1000],
            ['1970-01-01T00:00:00Z', '1970-01-01T14:00:00Z', Unit::SECOND, 14 * 3600],
            ['1970-01-01T00:00:00Z', '1970-01-01T14:00:00Z', Unit::MINUTE, 14 * 60],
            ['1970-01-01T00:00:00Z', '1970-01-01T14:00:00Z', Unit::HOUR, 14],

            ['1970-01-01T00:00:00Z', '1970-01-01T02:30:40.0015Z', Unit::MICRO, (2 * 3600 + 30 * 60 + 40) * 1000000 + 1500],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:30:40.0015Z', Unit::MILLI, (2 * 3600 + 30 * 60 + 40) * 1000 + 1],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:30:40.0015Z', Unit::SECOND, 2 * 3600 + 30 * 60 + 40],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:30:40.0015Z', Unit::MINUTE, 2 * 60 + 30],
            ['1970-01-01T00:00:00Z', '1970-01-01T02:30:40.0015Z', Unit::HOUR, 2],

            // combinations
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:39.000500Z', Unit::SECOND, -1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:39.000501Z', Unit::SECOND, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:40.000499Z', Unit::SECOND, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:40.000500Z', Unit::SECOND, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:40.000501Z', Unit::SECOND, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:41.000499Z', Unit::SECOND, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:41.000500Z', Unit::SECOND, 1],

            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:39.000499Z', Unit::SECOND, -2 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:39.000500Z', Unit::SECOND, -1 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:39.000501Z', Unit::SECOND, -1 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000499Z', Unit::SECOND, -1 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000500Z', Unit::SECOND, 0 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000501Z', Unit::SECOND, 0 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:41.000499Z', Unit::SECOND, 0 + 86400],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:41.000500Z', Unit::SECOND, 1 + 86400],

            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:29:40.000499Z', Unit::MINUTE, -2 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:29:40.000500Z', Unit::MINUTE, -1 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:29:40.000501Z', Unit::MINUTE, -1 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000499Z', Unit::MINUTE, -1 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000500Z', Unit::MINUTE, 0 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000501Z', Unit::MINUTE, 0 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:31:40.000499Z', Unit::MINUTE, 0 + 24 * 60],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:31:40.000500Z', Unit::MINUTE, 1 + 24 * 60],

            ['2000-01-15T12:30:40.000500Z', '2000-01-16T11:30:40.000499Z', Unit::HOUR, -2 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T11:30:40.000500Z', Unit::HOUR, -1 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T11:30:40.000501Z', Unit::HOUR, -1 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000499Z', Unit::HOUR, -1 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000500Z', Unit::HOUR, 0 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000501Z', Unit::HOUR, 0 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T13:30:40.000499Z', Unit::HOUR, 0 + 24],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T13:30:40.000500Z', Unit::HOUR, 1 + 24],

            ['2000-01-15T12:30:40.000500Z', '2000-01-13T12:30:40.000499Z', Unit::DAY, -2],
            ['2000-01-15T12:30:40.000500Z', '2000-01-13T12:30:40.000500Z', Unit::DAY, -2],
            ['2000-01-15T12:30:40.000500Z', '2000-01-13T12:30:40.000501Z', Unit::DAY, -1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-14T12:30:40.000499Z', Unit::DAY, -1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-14T12:30:40.000500Z', Unit::DAY, -1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-14T12:30:40.000501Z', Unit::DAY, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:40.000499Z', Unit::DAY, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:40.000500Z', Unit::DAY, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-15T12:30:40.000501Z', Unit::DAY, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000499Z', Unit::DAY, 0],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000500Z', Unit::DAY, 1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-16T12:30:40.000501Z', Unit::DAY, 1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-17T12:30:40.000499Z', Unit::DAY, 1],
            ['2000-01-15T12:30:40.000500Z', '2000-01-17T12:30:40.000500Z', Unit::DAY, 2],
            ['2000-01-15T12:30:40.000500Z', '2000-01-17T12:30:40.000501Z', Unit::DAY, 2]
        ];
    }
}
