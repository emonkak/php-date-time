<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\DateTime;
use Emonkak\DateTime\Duration;
use Emonkak\DateTime\Field;
use Emonkak\DateTime\Unit;
use Emonkak\DateTime\UnitInterface;

/**
 * @covers Emonkak\DateTime\DateTime
 */
class DateTimeTest extends AbstractTestCase
{
    /**
     * @dataProvider providerInvalidDateTimeString
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testInvalidDateTimeString(string $dateTimeString): void
    {
        new DateTime($dateTimeString);
    }

    public function providerInvalidDateTimeString(): array
    {
        return [
            ['0000-00-00 00:00:00']
        ];
    }

    /**
     * @dataProvider providerOf
     */
    public function testOf(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $this->assertDateTimeIs($y, $m, $d, $h, $i, $s, $c, $timeZone, DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone)));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetMicro(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $this->assertSame($c, DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone))->getMicro());
        $this->assertSame($c, DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone))->get(Field::microOfSecond()));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetSecond(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone));
        $this->assertSame($s, $dateTime->getSecond());
        $this->assertSame($s, $dateTime->get(Field::secondOfMinute()));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetMinute(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone));
        $this->assertSame($i, $dateTime->getMinute());
        $this->assertSame($i, $dateTime->get(Field::minuteOfHour()));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetHour(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone));
        $this->assertSame($h, $dateTime->getHour());
        $this->assertSame($h, $dateTime->get(Field::hourOfDay()));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetDayOfMonth(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone));
        $this->assertSame($d, $dateTime->getDayOfMonth());
        $this->assertSame($d, $dateTime->get(Field::dayOfMonth()));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetMonth(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone));
        $this->assertSame($m, $dateTime->getMonth());
        $this->assertSame($m, $dateTime->get(Field::monthOfYear()));
    }

    /**
     * @dataProvider providerOf
     */
    public function testGetYear(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $timeZone): void
    {
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, new \DateTimeZone($timeZone));
        $this->assertSame($y, $dateTime->getYear());
        $this->assertSame($y, $dateTime->get(Field::year()));
    }

    public function providerOf(): array
    {
        return [
            [2001, 12, 23, 12, 34, 56, 654321, '+09:00'],
            [9999, 12, 23, 12, 34, 56, 654321, '+09:00'],
            [-9999, 12, 23, 12, 34, 56, 654321, '+09:00']
        ];
    }

    /**
     * @dataProvider providerOfInvalidDateThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testOfInvalidDateThrowsException(int $y, int $m, int $d): void
    {
        DateTime::of($y, $m, $d);
    }

    public function providerOfInvalidDateThrowsException(): array
    {
        return [
            [2007, 2, 29],
            [2007, 4, 31],
            [2007, 1, 0],
            [2007, 1, 32],
            [2007, 0, 1],
            [2007, 13, 1],
            [-10000, 1, 1],
            [10000, 1, 1],
        ];
    }

    /**
     * @dataProvider providerOfEpochSecond
     */
    public function testOfEpochSecond(int $epochSecond, int $micro, string $offset, int $y, int $m, int $d, int $h, int $i, int $s, int $c): void
    {
        $timeZone = new \DateTimeZone($offset);
        $dateTime = DateTime::OfEpochSecond($epochSecond, $micro, $timeZone);

        $this->assertDateTimeIs($y, $m, $d, $h, $i, $s, $c, $offset, $dateTime);
    }

    public function providerOfEpochSecond(): array
    {
        return [
            [   1409574896,       0, '+00:00', 2014,  9,  1, 12, 34, 56,      0],
            [   1409574896,     123, '+00:00', 2014,  9,  1, 12, 34, 56,    123],
            [   1409574896,       0, '+01:00', 2014,  9,  1, 12, 34, 56,      0],
            [   1409574896,  123456, '+01:30', 2014,  9,  1, 12, 34, 56, 123456],
            [            0,       0, '+00:00', 1970,  1,  1,  0,  0,  0,      0],
            [            1,       1, '+00:00', 1970,  1,  1,  0,  0,  1,      1],
            [           -1,  999999, '+00:00', 1969, 12, 31, 23, 59, 59, 999999],
            [            1, -999999, '+00:00', 1970,  1,  1,  0,  0,  0,      1],
            [ 24 * 60 * 60,       0, '+00:00', 1970,  1,  2,  0,  0,  0,      0],
            [-24 * 60 * 60,       0, '+00:00', 1969, 12, 31,  0,  0,  0,      0],
            [-24 * 60 * 60,       0, '+00:00', 1969, 12, 31,  0,  0,  0,      0]
        ];
    }

    public function testFrom(): void
    {
        $dateTime = new DateTime('2000-01-01 00:00:00');
        $this->assertSame($dateTime, DateTime::from($dateTime));

        $dateTime = new \DateTime('2000-01-01 00:00:00');
        $this->assertNotSame($dateTime, DateTime::from($dateTime));
        $this->assertEquals($dateTime, DateTime::from($dateTime));

        $dateTime = new \DateTimeImmutable('2000-01-01 00:00:00');
        $this->assertNotSame($dateTime, DateTime::from($dateTime));
        $this->assertEquals($dateTime, DateTime::from($dateTime));
    }

    /**
     * @dataProvider providerGetDayOfWeek
     */
    public function testGetDayOfWeek(int $year, int $month, int $day, int $expectedDayOfWeek)
    {
        $dateTime = DateTime::of($year, $month, $day, 15, 30, 45);
        $this->assertDayOfWeekIs($expectedDayOfWeek, $dateTime->getDayOfWeek());
    }

    public function providerGetDayOfWeek(): array
    {
        return [
            [2000, 1, 3, 1],
            [2000, 2, 8, 2],
            [2000, 3, 8, 3],
            [2000, 4, 6, 4],
            [2000, 5, 5, 5],
            [2000, 6, 3, 6],
            [2000, 7, 9, 7],
            [2001, 1, 1, 1],
            [2001, 2, 6, 2],
            [2001, 3, 7, 3],
            [2001, 4, 5, 4],
            [2001, 5, 4, 5],
            [2001, 6, 9, 6],
            [2001, 7, 8, 7]
        ];
    }

    /**
     * @dataProvider providerDayOfYear
     */
    public function testGetDayOfYear(int $year, int $month, int $day, int $expectedDayOfYear): void
    {
        $this->assertSame($expectedDayOfYear, DateTime::of($year, $month, $day)->getDayOfYear());
    }

    public function providerDayOfYear(): array
    {
        return [
            [2000, 1, 1, 1],
            [2000, 1, 31, 31],
            [2000, 2, 1, 32],
            [2000, 2, 29, 60],
            [2000, 3, 1, 61],
            [2000, 3, 31, 91],
            [2000, 4, 1, 92],
            [2000, 4, 30, 121],
            [2000, 5, 1, 122],
            [2000, 5, 31, 152],
            [2000, 6, 1, 153],
            [2000, 6, 30, 182],
            [2000, 7, 1, 183],
            [2000, 7, 31, 213],
            [2000, 8, 1, 214],
            [2000, 8, 31, 244],
            [2000, 9, 1, 245],
            [2000, 9, 30, 274],
            [2000, 10, 1, 275],
            [2000, 10, 31, 305],
            [2000, 11, 1, 306],
            [2000, 11, 30, 335],
            [2000, 12, 1, 336],
            [2000, 12, 31, 366],
            [2001, 1, 1, 1],
            [2001, 1, 31, 31],
            [2001, 2, 1, 32],
            [2001, 2, 28, 59],
            [2001, 3, 1, 60],
            [2001, 3, 31, 90],
            [2001, 4, 1, 91],
            [2001, 4, 30, 120],
            [2001, 5, 1, 121],
            [2001, 5, 31, 151],
            [2001, 6, 1, 152],
            [2001, 6, 30, 181],
            [2001, 7, 1, 182],
            [2001, 7, 31, 212],
            [2001, 8, 1, 213],
            [2001, 8, 31, 243],
            [2001, 9, 1, 244],
            [2001, 9, 30, 273],
            [2001, 10, 1, 274],
            [2001, 10, 31, 304],
            [2001, 11, 1, 305],
            [2001, 11, 30, 334],
            [2001, 12, 1, 335],
            [2001, 12, 31, 365]
        ];
    }

    /**
     * @dataProvider providerIsLeapYear
     */
    public function testIsLeapYear(int $y, int $m, int $d, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, DateTime::of($y, $m, $d)->isLeapYear());
    }

    public function providerIsLeapYear(): array
    {
        return [
            [1600, 1, 11, true],
            [1700, 2, 12, false],
            [1800, 3, 13, false],
            [1900, 4, 14, false],
            [1999, 5, 15, false],
            [2000, 6, 16, true],
            [2004, 7, 17, true],
            [2007, 8, 18, false],
            [2008, 9, 18, true]
        ];
    }

    /**
     * @dataProvider providerWithMicro
     */
    public function testWithMicro(int $micro): void
    {
        $dateTime = DateTime::of(2001, 2, 3, 4, 5, 6, 123456);
        $this->assertDateTimeIs(2001, 2, 3, 4, 5, 6, $micro, '+00:00', $dateTime->withMicro($micro));
        $this->assertDateTimeIs(2001, 2, 3, 4, 5, 6, $micro, '+00:00', $dateTime->with(Field::microOfSecond(), $micro));
    }

    public function providerWithMicro(): array
    {
        return [
            [789],
            [123456]
        ];
    }

    /**
     * @dataProvider providerWithInvalidNanoThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidNanoThrowsException(int $invalidMicro): void
    {
        DateTime::of(2001, 2, 3, 4, 5, 6, 123456)->withMicro($invalidMicro);
    }

    public function providerWithInvalidNanoThrowsException(): array
    {
        return [
            [-1],
            [1000000]
        ];
    }

    /**
     * @dataProvider providerWithSecond
     */
    public function testWithSecond(int $second): void
    {
        $dateTime = DateTime::of(2001, 2, 3, 4, 5, 6, 123456);
        $this->assertDateTimeIs(2001, 2, 3, 4, 5, $second, 123456, '+00:00', $dateTime->withSecond($second));
        $this->assertDateTimeIs(2001, 2, 3, 4, 5, $second, 123456, '+00:00', $dateTime->with(Field::secondOfMinute(), $second));
    }

    public function providerWithSecond(): array
    {
        return [
            [56],
            [45]
        ];
    }

    /**
     * @dataProvider providerWithInvalidSecondThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidSecondThrowsException(int $invalidSecond): void
    {
        DateTime::of(2001, 2, 3, 4, 5, 6, 123456)->withSecond($invalidSecond);
    }

    public function providerWithInvalidSecondThrowsException(): array
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithMinute
     */
    public function testWithMinute(int $minute): void
    {
        $dateTime = DateTime::of(2001, 2, 3, 4, 5, 6, 123456);
        $this->assertDateTimeIs(2001, 2, 3, 4, $minute, 6, 123456, '+00:00', $dateTime->withMinute($minute));
        $this->assertDateTimeIs(2001, 2, 3, 4, $minute, 6, 123456, '+00:00', $dateTime->with(Field::minuteOfHour(), $minute));
    }

    public function providerWithMinute(): array
    {
        return [
            [34],
            [45]
        ];
    }

    /**
     * @dataProvider providerWithInvalidMinuteThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidMinuteThrowsException(int $invalidMinute): void
    {
        DateTime::of(2001, 2, 3, 4, 5, 6, 123456)->withMinute($invalidMinute);
    }

    public function providerWithInvalidMinuteThrowsException(): array
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithHour
     */
    public function testWithHour(int $hour): void
    {
        $dateTime = DateTime::of(2001, 2, 3, 4, 5, 6, 123456);
        $this->assertDateTimeIs(2001, 2, 3, $hour, 5, 6, 123456, '+00:00', $dateTime->withHour($hour));
        $this->assertDateTimeIs(2001, 2, 3, $hour, 5, 6, 123456, '+00:00', $dateTime->with(Field::hourOfDay(), $hour));
    }

    public function providerWithHour(): array
    {
        return [
            [12],
            [23]
        ];
    }

    /**
     * @dataProvider providerWithInvalidHourThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidHourThrowsException(int $invalidHour): void
    {
        DateTime::of(2001, 2, 3, 4, 5, 6, 123456)->withHour($invalidHour);
    }

    public function providerWithInvalidHourThrowsException() : array
    {
        return [
            [-1],
            [24]
        ];
    }

    /**
     * @dataProvider providerWithDay
     */
    public function testWithDay(int $year, int $month, int $day, int $newDay): void
    {
        $dateTime = DateTime::of($year, $month, $day, 1, 2, 3, 123456);
        $this->assertDateTimeIs($year, $month, $newDay, 1, 2, 3, 123456, '+00:00', $dateTime->withDay($newDay));
        $this->assertDateTimeIs($year, $month, $newDay, 1, 2, 3, 123456, '+00:00', $dateTime->with(Field::dayOfMonth(), $newDay));
    }

    public function providerWithDay(): array
    {
        return [
            [2007, 6, 2, 2],
            [2007, 1, 1, 31],
            [2008, 2, 28, 29],
            [2010, 2, 27, 28]
        ];
    }

    /**
     * @dataProvider providerWithInvalidDayThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidDayThrowsException(int $year, int $month, int $day, int $invalidDay)
    {
        DateTime::of($year, $month, $day, 1, 2, 3, 123456)->withDay($invalidDay);
    }

    public function providerWithInvalidDayThrowsException(): array
    {
        return [
            [2007, 1, 1, 0],
            [2007, 1, 1, 32],
            [2007, 2, 1, 29],
            [2008, 2, 1, 30],
            [2009, 4, 1, 31]
        ];
    }

    /**
     * @dataProvider providerWithMonth
     */
    public function testWithMonth(int $year, int $month, int $day, int $newMonth, int $expectedDay): void
    {
        $dateTime = DateTime::of($year, $month, $day, 1, 2, 3, 123456);
        $this->assertDateTimeIs($year, $newMonth, $expectedDay, 1, 2, 3, 123456, '+00:00', $dateTime->withMonth($newMonth));
        $this->assertDateTimeIs($year, $newMonth, $expectedDay, 1, 2, 3, 123456, '+00:00', $dateTime->with(Field::monthOfYear(), $newMonth));
    }

    public function providerWithMonth(): array
    {
        return [
            [2007, 3, 31, 2, 28],
            [2008, 3, 31, 2, 29],
            [2007, 3, 31, 1, 31],
            [2008, 3, 31, 3, 31],
            [2007, 3, 31, 4, 30],
            [2008, 3, 31, 5, 31],
            [2007, 3, 31, 6, 30],
            [2008, 3, 31, 7, 31],
            [2007, 3, 31, 8, 31],
            [2008, 3, 31, 9, 30],
            [2007, 3, 31, 10, 31],
            [2008, 3, 31, 11, 30],
            [2007, 3, 31, 12, 31],
            [2008, 4, 30, 12, 30]
        ];
    }

    /**
     * @dataProvider providerWithInvalidMonthThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidMonthThrowsException(int $invalidMonth): void
    {
        DateTime::of(2001, 2, 3, 4, 5, 6, 123456)->withMonth($invalidMonth);
    }

    public function providerWithInvalidMonthThrowsException(): array
    {
        return [
            [0],
            [13]
        ];
    }

    /**
     * @dataProvider providerWithYear
     */
    public function testWithYear(int $year, int $month, int $day, int $newYear, int $expectedDay): void
    {
        $dateTime = DateTime::of($year, $month, $day, 1, 2, 3, 123456);
        $this->assertDateTimeIs($newYear, $month, $expectedDay, 1, 2, 3, 123456, '+00:00', $dateTime->withYear($newYear));
        $this->assertDateTimeIs($newYear, $month, $expectedDay, 1, 2, 3, 123456, '+00:00', $dateTime->with(Field::year(), $newYear));
    }

    public function providerWithYear(): array
    {
        return [
            [2007, 3, 31, 2008, 31],
            [2007, 2, 28, 2008, 28],
            [2008, 2, 28, 2009, 28],
            [2008, 2, 29, 2008, 29],
            [2008, 2, 29, 2009, 28],
            [2008, 2, 29, 2012, 29]
        ];
    }

    /**
     * @dataProvider providerWithInvalidYearThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testWithInvalidYearThrowsException(int $invalidYear)
    {
        DateTime::of(2001, 2, 3, 4, 5, 6, 123456)->withYear($invalidYear);
    }

    public function providerWithInvalidYearThrowsException() : array
    {
        return [
            [-1000000],
            [1000000]
        ];
    }

    /**
     * @dataProvider providerWithTimeZone
     */
    public function testWithTimeZone(string $dateTimeString, string $timeZone, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString))->withTimeZone(new \DateTimeZone($timeZone));
        $this->assertSame($expectedDateTime, (string) $dateTime);
    }

    public function providerWithTimeZone(): array
    {
        return [
            ['2001-02-03T04:05:06.123456Z', '+00:00', '2001-02-03T04:05:06.123456Z'],
            ['2001-02-03T04:05:06.123456Z', '+09:00', '2001-02-03T13:05:06.123456+09:00']
        ];
    }

    /**
     * @dataProvider providerDuration
     */
    public function testPlusDuration(int $seconds, int $micros, int $ey, int $em, int $ed, int $eh, int $ei, int $es, int $en)
    {
        $dateTime = DateTime::of(2001, 2, 3, 4, 5, 6, 123456);
        $duration = Duration::ofSeconds($seconds, $micros);
        $this->assertDateTimeIs($ey, $em, $ed, $eh, $ei, $es, $en, '+00:00', $dateTime->plusDuration($duration));
    }

    /**
     * @dataProvider providerDuration
     */
    public function testMinusDuration(int $seconds, int $micros, int $ey, int $em, int $ed, int $eh, int $ei, int $es, int $en)
    {
        $dateTime = DateTime::of(2001, 2, 3, 4, 5, 6, 123456);
        $duration = Duration::ofSeconds(-$seconds, -$micros);
        $this->assertDateTimeIs($ey, $em, $ed, $eh, $ei, $es, $en, '+00:00', $dateTime->minusDuration($duration));
    }

    public function providerDuration(): array
    {
        return [
            [0, 0, 2001, 2, 3, 4, 5, 6, 123456],
            [123456, 2000000, 2001, 2, 4, 14, 22, 44, 123456],
            [7654321, 1999999, 2001, 5, 2, 18, 17, 9, 123455],
            [-654321, -987654, 2001, 1, 26, 14, 19, 44, 135802],
            [-7654321, 2013456, 2000, 11, 6, 13, 53, 7, 136912]
        ];
    }

    /**
     * @dataProvider providerMicros
     */
    public function testPlusMicros(string $dateTimeString, int $micros, string $expectedDateTime): void
    {
        $dateTime = new DateTime($dateTimeString);
        $this->assertSame($expectedDateTime, (string) $dateTime->plusMicros($micros));
        $this->assertSame($expectedDateTime, (string) $dateTime->plus($micros, Unit::micro()));
    }

    /**
     * @dataProvider providerMicros
     */
    public function testMinusMicros(string $dateTimeString, int $micros, string $expectedDateTime): void
    {
        $dateTime = new DateTime($dateTimeString);
        $this->assertSame($expectedDateTime, (string) $dateTime->minusMicros(-$micros));
        $this->assertSame($expectedDateTime, (string) $dateTime->minus(-$micros, Unit::micro()));
    }

    public function providerMicros(): array
    {
        return [
            ['2000-03-01T00:00:00Z', 0, '2000-03-01T00:00:00Z'],
            ['2014-12-31T23:59:58.5Z', 1500000, '2015-01-01T00:00:00Z'],
            ['2000-03-01T00:00:00Z', -1, '2000-02-29T23:59:59.999999Z'],
            ['2000-01-01T00:00:01Z', -1999999, '1999-12-31T23:59:59.000001Z']
        ];
    }

    /**
     * @dataProvider providerSeconds
     */
    public function testPlusSeconds(string $dateTimeString, int $seconds, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusSeconds($seconds));
        $this->assertSame($expectedDateTime, (string) $dateTime->plus($seconds, Unit::second()));
    }

    /**
     * @dataProvider providerSeconds
     */
    public function testMinusSeconds(string $dateTimeString, int $seconds, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusSeconds(-$seconds));
        $this->assertSame($expectedDateTime, (string) $dateTime->minus(-$seconds, Unit::second()));
    }

    public function providerSeconds(): array
    {
        return [
            ['1999-11-30T12:34:56Z', 0, '1999-11-30T12:34:56Z'],
            ['1999-11-30T12:34:56Z', 123456789, '2003-10-29T10:08:05Z'],
            ['2000-11-30T12:34:56.123456Z', -987654321, '1969-08-14T08:09:35.123456Z']
        ];
    }

    /**
     * @dataProvider providerMinutes
     */
    public function testPlusMinutes(string $dateTimeString, int $minutes, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusMinutes($minutes));
        $this->assertSame($expectedDateTime, (string) $dateTime->plus($minutes, Unit::minute()));
    }

    /**
     * @dataProvider providerMinutes
     */
    public function testMinusMinutes(string $dateTimeString, int $minutes, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusMinutes(-$minutes));
        $this->assertSame($expectedDateTime, (string) $dateTime->minus(-$minutes, Unit::minute()));
    }

    public function providerMinutes(): array
    {
        return [
            ['1999-11-30T12:34:56Z', 0, '1999-11-30T12:34:56Z'],
            ['1999-11-30T12:34:56Z', 123456789, '2234-08-24T09:43:56Z'],
            ['2000-11-30T12:34:56.123456Z', -987654321, '0123-01-24T11:13:56.123456Z']
        ];
    }

    /**
     * @dataProvider providerHours
     */
    public function testPlusHours(string $dateTimeString, int $hours, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusHours($hours));
        $this->assertSame($expectedDateTime, (string) $dateTime->plus($hours, Unit::hour()));
    }

    /**
     * @dataProvider providerHours
     */
    public function testMinusHours(string $dateTimeString, int $hours, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusHours(-$hours));
        $this->assertSame($expectedDateTime, (string) $dateTime->minus(-$hours, Unit::hour()));
    }

    public function providerHours(): array
    {
        return [
            ['1999-11-30T12:34:56Z', 0, '1999-11-30T12:34:56Z'],
            ['1999-11-30T12:34:56Z', 123456, '2013-12-30T12:34:56Z'],
            ['2000-11-30T12:34:56.123456Z', -654321, '1926-04-10T03:34:56.123456Z']
        ];
    }

    /**
     * @dataProvider providerDays
     */
    public function testPlusDays(string $dateTimeString, int $days, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusDays($days));
        $this->assertSame($expectedDateTime, (string) $dateTime->plus($days, Unit::day()));
    }

    /**
     * @dataProvider providerDays
     */
    public function testMinusDays(string $dateTimeString, int $days, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusDays(-$days));
        $this->assertSame($expectedDateTime, (string) $dateTime->minus(-$days, Unit::day()));
    }

    public function providerDays(): array
    {
        return [
            ['1999-11-30T12:34Z', 0, '1999-11-30T12:34:00Z'],
            ['1999-11-30T12:34Z', 5000, '2013-08-08T12:34:00Z'],
            ['2000-11-30T12:34:56.123456Z', -500, '1999-07-19T12:34:56.123456Z']
        ];
    }

    /**
     * @dataProvider providerWeeks
     */
    public function testPlusWeeks(string $dateTimeString, int $weeks, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusWeeks($weeks));
        $this->assertSame($expectedDateTime, (string) $dateTime->plus($weeks, Unit::week()));
    }

    /**
     * @dataProvider providerWeeks
     */
    public function testMinusWeeks(string $dateTimeString, int $weeks, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusWeeks(-$weeks));
        $this->assertSame($expectedDateTime, (string) $dateTime->minus(-$weeks, Unit::week()));
    }

    public function providerWeeks(): array
    {
        return [
            ['1999-11-30T12:34:00Z', 0, '1999-11-30T12:34:00Z'],
            ['1999-11-30T12:34:00Z', 714, '2013-08-06T12:34:00Z'],
            ['2000-11-30T12:34:56.123456Z', -71, '1999-07-22T12:34:56.123456Z']
        ];
    }

    /**
     * @dataProvider providerMonths
     */
    public function testPlusMonths(string $dateTimeString, int $months, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusMonths($months));
    }

    /**
     * @dataProvider providerMonths
     */
    public function testMinusMonths(string $dateTimeString, int $months, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusMonths(-$months));
    }

    public function providerMonths(): array
    {
        return [
            ['2001-01-31T12:34:56Z', 0, '2001-01-31T12:34:56Z'],
            ['2001-01-31T12:34:56Z', 1, '2001-02-28T12:34:56Z'],
            ['2001-04-30T12:34:56.123456Z', -14, '2000-02-29T12:34:56.123456Z']
        ];
    }

    /**
     * @dataProvider providerYears
     */
    public function testPlusYears(string $dateTimeString, int $years, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->plusYears($years));
    }

    /**
     * @dataProvider providerYears
     */
    public function testMinusYears(string $dateTimeString, int $years, string $expectedDateTime): void
    {
        $dateTime = (new DateTime($dateTimeString));
        $this->assertSame($expectedDateTime, (string) $dateTime->minusYears(-$years));
    }

    public function providerYears(): array
    {
        return [
            ['2000-02-29T12:34:00Z', 0, '2000-02-29T12:34:00Z'],
            ['2001-02-23T12:34:56.123456Z', 1, '2002-02-23T12:34:56.123456Z'],
            ['2000-02-29T12:34:00Z', -1, '1999-02-28T12:34:00Z']
        ];
    }

    public function testUntil(): void
    {
        $startInclusive = new DateTime('2001-02-03T04:05:06.123456Z');
        $endExclusive = new DateTime('2001-02-03T04:05:07.123456Z');
        $unit = $this->createMock(UnitInterface::class);
        $unit
            ->expects($this->once())
            ->method('between')
            ->with(
                $this->identicalTo($startInclusive),
                $this->identicalTo($endExclusive)
            )
            ->willReturn(1);
        $this->assertSame(1, $startInclusive->until($endExclusive, $unit));
    }

    /**
     * @dataProvider providerToEpochSecond
     */
    public function testToEpochSecond(int $y, int $m, int $d, int $h, int $i, int $s, int $c, string $offset, int $expectedEpochSecond): void
    {
        $timeZone = new \DateTimeZone($offset);
        $dateTime = DateTime::of($y, $m, $d, $h, $i, $s, $c, $timeZone);
        $this->assertSame($expectedEpochSecond, $dateTime->toEpochSecond());
    }

    public function providerToEpochSecond(): array
    {
        return [
            [2014,  9,  1, 12, 34, 56,      0, '+00:00',    1409574896],
            [2014,  9,  1, 12, 34, 56,    123, '+00:00',    1409574896],
            [2014,  9,  1, 12, 34, 56,      0, '+01:00',    1409574896],
            [1970,  1,  1,  0,  0,  0,      0, '+00:00',             0],
            [1970,  1,  1,  0,  0,  1,      1, '+00:00',             1],
            [1970,  1,  1,  0,  0,  0,      1, '+00:00',             0],
            [1970,  1,  2,  0,  0,  0,      0, '+00:00',  24 * 60 * 60],
            [1969, 12, 31,  0,  0,  0,      0, '+00:00', -24 * 60 * 60],
            [1969, 12, 31,  0,  0,  0,      0, '+00:00', -24 * 60 * 60]
        ];
    }

    /**
     * @dataProvider providerToDateString
     */
    public function testToDateString(string $dateTimeString, string $expectedString): void
    {
        $this->assertSame($expectedString, (new DateTime($dateTimeString))->toDateString());
    }

    public function providerToDateString(): array
    {
        return [
            ['2001-12-23T12:34:56.654321+09:00', '2001-12-23']
        ];
    }

    /**
     * @dataProvider providerToDateTimeString
     */
    public function testToDateTimeString(string $dateTimeString, string $expectedString): void
    {
        $this->assertSame($expectedString, (new DateTime($dateTimeString))->toDateTimeString());
    }

    public function providerToDateTimeString(): array
    {
        return [
            ['2001-12-23T12:34:56+09:00', '2001-12-23 12:34:56'],
            ['2001-12-23T12:34:56.654321+09:00', '2001-12-23 12:34:56.654321']
        ];
    }

    /**
     * @dataProvider providerToTimeString
     */
    public function testToTimeString(string $dateTimeString, string $expectedString): void
    {
        $this->assertSame($expectedString, (new DateTime($dateTimeString))->toTimeString());
    }

    public function providerToTimeString(): array
    {
        return [
            ['2001-12-23T12:34:56+09:00', '12:34:56'],
            ['2001-12-23T12:34:56.654321+09:00', '12:34:56.654321']
        ];
    }

    /**
     * @dataProvider providerJsonSerialize
     */
    public function testJsonSerialize(string $dateTimeString, string $expectedString): void
    {
        $this->assertSame($expectedString, json_encode(new DateTime($dateTimeString)));
    }

    public function providerJsonSerialize(): array
    {
        return [
            ['2001-02-03T04:05:06.123456+09:00', '"2001-02-03T04:05:06.123456+09:00"'],
        ];
    }
}
