<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Field;
use Emonkak\DateTime\DateTime;
use Emonkak\DateTime\Unit;

/**
 * @covers Emonkak\DateTime\Field
 */
class FieldTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     * @runInSeparateProcess
     */
    public function testOf(string $name, string $expectedBaseUnit, string $expectedRangeUnit, int $expectedMinValue, int $expectedMaxValue): void
    {
        $field = Field::of($name);
        $this->assertFieldIs($name, $field);
        $this->assertUnitIs($expectedBaseUnit, $field->getBaseUnit());
        $this->assertUnitIs($expectedRangeUnit, $field->getRangeUnit());
        $this->assertSame($expectedMinValue, $field->getMinValue());
        $this->assertSame($expectedMaxValue, $field->getMaxValue());
    }

    public function providerOf(): array
    {
        return [
            [Field::MICRO_OF_SECOND, Unit::MICRO, Unit::SECOND, 0, 999999],
            [Field::MILLI_OF_SECOND, Unit::MILLI, Unit::SECOND, 0, 999],
            [Field::SECOND_OF_MINUTE, Unit::SECOND, Unit::MINUTE, 0, 59],
            [Field::SECOND_OF_DAY, Unit::SECOND, Unit::DAY, 0, (24 * 60 * 60) - 1],
            [Field::MINUTE_OF_HOUR, Unit::MINUTE, Unit::HOUR, 0, 59],
            [Field::HOUR_OF_DAY, Unit::HOUR, Unit::DAY, 0, 23],
            [Field::DAY_OF_WEEK, Unit::DAY, Unit::WEEK, 1, 7],
            [Field::DAY_OF_MONTH, Unit::DAY, Unit::MONTH, 1, 31],
            [Field::DAY_OF_YEAR, Unit::DAY, Unit::YEAR, 1, 366],
            [Field::MONTH_OF_YEAR, Unit::MONTH, Unit::YEAR, 1, 12],
            [Field::YEAR, Unit::YEAR, Unit::FOREVER, -9999, 9999]
        ];
    }

    /**
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testOfInvalidNameThrowsException(): void
    {
        Field::of('invalid');
    }

    public function testCheck(): void
    {
        Field::secondOfMinute()->check(0);

        $this->assertTrue(true);
    }

    /**
     * @expectedException Emonkak\Datetime\DateTimeException
     */
    public function testCheckWithOutOfRangeValue(): void
    {
        Field::secondOfMinute()->check(60);
    }

    public function testMicroOfSecond(): void
    {
        $this->assertFieldIs(Field::MICRO_OF_SECOND, Field::microOfSecond());
    }

    public function testMilliOfSecond(): void
    {
        $this->assertFieldIs(Field::MILLI_OF_SECOND, Field::milliOfSecond());
    }

    public function testSecondOfMinute(): void
    {
        $this->assertFieldIs(Field::SECOND_OF_MINUTE, Field::secondOfMinute());
    }

    public function testSecondOfDay(): void
    {
        $this->assertFieldIs(Field::SECOND_OF_DAY, Field::secondOfDay());
    }

    public function testMinuteOfHour(): void
    {
        $this->assertFieldIs(Field::MINUTE_OF_HOUR, Field::minuteOfHour());
    }

    public function testHourOfDay(): void
    {
        $this->assertFieldIs(Field::HOUR_OF_DAY, Field::hourOfDay());
    }

    public function testDayOfWeek(): void
    {
        $this->assertFieldIs(Field::DAY_OF_WEEK, Field::dayOfWeek());
    }

    public function testDayOfMonth(): void
    {
        $this->assertFieldIs(Field::DAY_OF_MONTH, Field::dayOfMonth());
    }

    public function testDayOfYear(): void
    {
        $this->assertFieldIs(Field::DAY_OF_YEAR, Field::dayOfYear());
    }

    public function testMonthOfYear(): void
    {
        $this->assertFieldIs(Field::MONTH_OF_YEAR, Field::monthOfYear());
    }

    public function testYear(): void
    {
        $this->assertFieldIs(Field::YEAR, Field::year());
    }

    /**
     * @dataProvider providerGetFrom
     */
    public function testGetFrom(string $field, int $expectedValue): void
    {
        $dateTime = new \DateTimeImmutable('2001-02-03T04:05:06.123456Z');
        $this->assertSame($expectedValue, Field::of($field)->getFrom($dateTime));
    }

    public function providerGetFrom(): array
    {
        return [
            [Field::MICRO_OF_SECOND, 123456],
            [Field::MILLI_OF_SECOND, 123],
            [Field::SECOND_OF_MINUTE, 6],
            [Field::SECOND_OF_DAY, 14706],
            [Field::MINUTE_OF_HOUR, 5],
            [Field::HOUR_OF_DAY, 4],
            [Field::DAY_OF_WEEK, 6],
            [Field::DAY_OF_MONTH, 3],
            [Field::DAY_OF_YEAR, 34],
            [Field::MONTH_OF_YEAR, 2],
            [Field::YEAR, 2001]
        ];
    }

    /**
     * @dataProvider providerAdjustInto
     */
    public function testAdjustInto(string $field, int $newValue, string $expectedDateTime): void
    {
        $dateTime = new \DateTimeImmutable('2001-02-03T04:05:06.123456Z');
        $actualDateTime = Field::of($field)->adjustInto($dateTime, $newValue);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    public function providerAdjustInto(): array
    {
        return [
            [Field::MICRO_OF_SECOND, 1, '2001-02-03T04:05:06.000001Z'],
            [Field::MILLI_OF_SECOND, 1, '2001-02-03T04:05:06.001Z'],
            [Field::SECOND_OF_MINUTE, 1, '2001-02-03T04:05:01.123456Z'],
            [Field::SECOND_OF_DAY, 1, '2001-02-03T00:00:01.123456Z'],
            [Field::MINUTE_OF_HOUR, 1, '2001-02-03T04:01:06.123456Z'],
            [Field::HOUR_OF_DAY, 1, '2001-02-03T01:05:06.123456Z'],
            [Field::DAY_OF_WEEK, 1, '2001-01-29T04:05:06.123456Z'],
            [Field::DAY_OF_MONTH, 1, '2001-02-01T04:05:06.123456Z'],
            [Field::DAY_OF_YEAR, 1, '2001-01-01T04:05:06.123456Z'],
            [Field::MONTH_OF_YEAR, 1, '2001-01-03T04:05:06.123456Z'],
            [Field::YEAR, 1, '0001-02-03T04:05:06.123456Z'],
        ];
    }
}
