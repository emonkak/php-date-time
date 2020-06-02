<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * A field of date-time, such as month-of-year or hour-of-minute.
 */
final class Field implements FieldInterface
{
    const MICRO_OF_SECOND = 'micro-of-second';
    const MILLI_OF_SECOND = 'milli-of-second';
    const SECOND_OF_MINUTE = 'second-of-minute';
    const SECOND_OF_DAY = 'second-of-day';
    const MINUTE_OF_HOUR = 'minute-of-hour';
    const HOUR_OF_DAY = 'hour-of-day';
    const DAY_OF_WEEK = 'day-of-week';
    const DAY_OF_MONTH = 'day-of-month';
    const DAY_OF_YEAR = 'day-of-year';
    const MONTH_OF_YEAR = 'month-of-year';
    const YEAR = 'year';

    /**
     * @var self::*
     */
    private $name;

    /**
     * @var UnitInterface
     */
    private $baseUnit;

    /**
     * @var UnitInterface
     */
    private $rangeUnit;

    /**
     * @var int
     */
    private $minValue;

    /**
     * @var int
     */
    private $maxValue;

    public static function of(string $name): Field
    {
        static $values;

        if (!isset($values[$name])) {
            $values[$name] = self::create($name);
        }

        return $values[$name];
    }

    public static function check(FieldInterface $field, int $value): void
    {
        if (!$field->validate($value)) {
            throw new DateTimeException(sprintf('Invalid %s field: %d is not in the range %d to %d.', $field->__toString(), $value, $field->getMinValue(), $field->getMaxValue()));
        }
    }

    public static function microOfSecond(): Field
    {
        return self::of(self::MICRO_OF_SECOND);
    }

    public static function milliOfSecond(): Field
    {
        return self::of(self::MILLI_OF_SECOND);
    }

    public static function secondOfMinute(): Field
    {
        return self::of(self::SECOND_OF_MINUTE);
    }

    public static function secondOfDay(): Field
    {
        return self::of(self::SECOND_OF_DAY);
    }

    public static function minuteOfHour(): Field
    {
        return self::of(self::MINUTE_OF_HOUR);
    }

    public static function hourOfDay(): Field
    {
        return self::of(self::HOUR_OF_DAY);
    }

    public static function dayOfWeek(): Field
    {
        return self::of(self::DAY_OF_WEEK);
    }

    public static function dayOfMonth(): Field
    {
        return self::of(self::DAY_OF_MONTH);
    }

    public static function dayOfYear(): Field
    {
        return self::of(self::DAY_OF_YEAR);
    }

    public static function monthOfYear(): Field
    {
        return self::of(self::MONTH_OF_YEAR);
    }

    public static function year(): Field
    {
        return self::of(self::YEAR);
    }

    private static function create(string $name): Field
    {
        switch ($name) {
            case self::MICRO_OF_SECOND:
                return new Field($name, Unit::micro(), Unit::second(), 0, 999999);

            case self::MILLI_OF_SECOND:
                return new Field($name, Unit::milli(), Unit::second(), 0, 999);

            case self::SECOND_OF_MINUTE:
                return new Field($name, Unit::second(), Unit::minute(), 0, 59);

            case self::SECOND_OF_DAY:
                return new Field($name, Unit::second(), Unit::day(), 0, (24 * 60 * 60) - 1);

            case self::MINUTE_OF_HOUR:
                return new Field($name, Unit::minute(), Unit::hour(), 0, 59);

            case self::HOUR_OF_DAY:
                return new Field($name, Unit::hour(), Unit::day(), 0, 23);

            case self::DAY_OF_WEEK:
                return new Field($name, Unit::day(), Unit::week(), 1, 7);

            case self::DAY_OF_MONTH:
                return new Field($name, Unit::day(), Unit::month(), 1, 31);

            case self::DAY_OF_YEAR:
                return new Field($name, Unit::day(), Unit::year(), 1, 366);

            case self::MONTH_OF_YEAR:
                return new Field($name, Unit::month(), Unit::year(), 1, 12);

            case self::YEAR:
                return new Field($name, Unit::year(), Unit::forever(), -9999, 9999);

            default:
                throw new DateTimeException('Invalid name for field: ' . $name);
        }
    }

    /**
     * @param self::* $name
     */
    private function __construct(string $name, UnitInterface $baseUnit, UnitInterface $rangeUnit, int $minValue, int $maxValue)
    {
        $this->name = $name;
        $this->baseUnit = $baseUnit;
        $this->rangeUnit = $rangeUnit;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    public function getBaseUnit(): UnitInterface
    {
        return $this->baseUnit;
    }

    public function getRangeUnit(): UnitInterface
    {
        return $this->rangeUnit;
    }

    public function getMinValue(): int
    {
        return $this->minValue;
    }

    public function getMaxValue(): int
    {
        return $this->maxValue;
    }

    public function getFrom(\DateTimeInterface $dateTime): int
    {
        $dateTime = DateTime::from($dateTime);

        switch ($this->name) {
            case self::MICRO_OF_SECOND:
                return $dateTime->getMicro();

            case self::MILLI_OF_SECOND:
                return intdiv($dateTime->getMicro(), 1000);

            case self::SECOND_OF_MINUTE:
                return $dateTime->getSecond();

            case self::SECOND_OF_DAY:
                return $dateTime->getHour() * DateTime::SECONDS_PER_HOUR + $dateTime->getMinute() * DateTime::SECONDS_PER_MINUTE + $dateTime->getSecond();

            case self::MINUTE_OF_HOUR:
                return $dateTime->getMinute();

            case self::HOUR_OF_DAY:
                return $dateTime->getHour();

            case self::DAY_OF_WEEK:
                return $dateTime->getDayOfWeek()->getValue();

            case self::DAY_OF_MONTH:
                return $dateTime->getDayOfMonth();

            case self::DAY_OF_YEAR:
                return $dateTime->getDayOfYear();

            case self::MONTH_OF_YEAR:
                return $dateTime->getMonth();

            case self::YEAR:
                return $dateTime->getYear();
        }
    }

    public function adjustInto(\DateTimeInterface $dateTime, int $newValue): DateTime
    {
        $dateTime = DateTime::from($dateTime);

        switch ($this->name) {
            case self::MICRO_OF_SECOND:
                return $dateTime->withMicro($newValue);

            case self::MILLI_OF_SECOND:
                return $dateTime->withMicro($newValue * 1000);

            case self::SECOND_OF_MINUTE:
                return $dateTime->withSecond($newValue);

            case self::SECOND_OF_DAY:
                return $dateTime->plusSeconds($newValue - $this->getFrom($dateTime));

            case self::MINUTE_OF_HOUR:
                return $dateTime->withMinute($newValue);

            case self::HOUR_OF_DAY:
                return $dateTime->withHour($newValue);

            case self::DAY_OF_WEEK:
                return $dateTime->plusDays($newValue - $dateTime->getDayOfWeek()->getValue());

            case self::DAY_OF_MONTH:
                return $dateTime->withDay($newValue);

            case self::DAY_OF_YEAR:
                return $dateTime->plusDays($newValue - $dateTime->getDayOfYear());

            case self::MONTH_OF_YEAR:
                return $dateTime->withMonth($newValue);

            case self::YEAR:
                return $dateTime->withYear($newValue);
        }
    }

    public function validate(int $value): bool
    {
        return $this->minValue <= $value && $value <= $this->maxValue;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
