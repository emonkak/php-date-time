<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * A unit of date-time, such as Days or Hours.
 */
final class Unit implements UnitInterface
{
    const MICRO = 'micro';
    const MILLI = 'milli';
    const SECOND = 'second';
    const MINUTE = 'minute';
    const HOUR = 'hour';
    const DAY = 'day';
    const WEEK = 'week';
    const MONTH = 'month';
    const YEAR = 'year';
    const FOREVER = 'forever';

    /**
     * @var self::*
     */
    private $name;

    /**
     * @var Duration
     */
    private $duration;

    public static function of(string $name): Unit
    {
        static $values;

        if (!isset($values[$name])) {
            $values[$name] = self::create($name);
        }

        return $values[$name];
    }

    public static function micro(): Unit
    {
        return self::of(self::MICRO);
    }

    public static function milli(): Unit
    {
        return self::of(self::MILLI);
    }

    public static function second(): Unit
    {
        return self::of(self::SECOND);
    }

    public static function minute(): Unit
    {
        return self::of(self::MINUTE);
    }

    public static function hour(): Unit
    {
        return self::of(self::HOUR);
    }

    public static function day(): Unit
    {
        return self::of(self::DAY);
    }

    public static function week(): Unit
    {
        return self::of(self::WEEK);
    }

    public static function month(): Unit
    {
        return self::of(self::MONTH);
    }

    public static function year(): Unit
    {
        return self::of(self::YEAR);
    }

    public static function forever(): Unit
    {
        return self::of(self::FOREVER);
    }

    private static function create(string $name): Unit
    {
        switch ($name) {
            case self::MICRO:
                return new Unit($name, Duration::ofSeconds(0, 1));

            case self::MILLI:
                return new Unit($name, Duration::ofSeconds(0, 1000));

            case self::SECOND:
                return new Unit($name, Duration::ofSeconds(1));

            case self::MINUTE:
                return new Unit($name, Duration::ofSeconds(60));

            case self::HOUR:
                return new Unit($name, Duration::ofSeconds(60 * 60));

            case self::DAY:
                return new Unit($name, Duration::ofSeconds(60 * 60 * 24));

            case self::WEEK:
                return new Unit($name, Duration::ofSeconds(60 * 60 * 24 * 7));

            case self::MONTH:
                return new Unit($name, Duration::ofSeconds(intdiv(31556952, 12)));

            case self::YEAR:
                return new Unit($name, Duration::ofSeconds(31556952));

            case self::FOREVER:
                return new Unit($name, Duration::ofSeconds(PHP_INT_MAX, 999999));

            default:
                throw new DateTimeException('Invalid name for Unit: ' . $name);
        }
    }

    /**
     * @param self::* $name
     */
    private function __construct(string $name, Duration $duration)
    {
        $this->name = $name;
        $this->duration = $duration;
    }

    public function addTo(\DateTimeInterface $dateTime, int $amount): DateTime
    {
        switch ($this->name) {
            case self::MICRO:
                return DateTime::from($dateTime)->plusMicros($amount);

            case self::MILLI:
                return DateTime::from($dateTime)->plusMicros($amount * 1000);

            case self::SECOND:
                return DateTime::from($dateTime)->plusSeconds($amount);

            case self::MINUTE:
                return DateTime::from($dateTime)->plusMinutes($amount);

            case self::HOUR:
                return DateTime::from($dateTime)->plusHours($amount);

            case self::DAY:
                return DateTime::from($dateTime)->plusDays($amount);

            case self::WEEK:
                return DateTime::from($dateTime)->plusWeeks($amount);

            case self::MONTH:
                return DateTime::from($dateTime)->plusMonths($amount);

            case self::YEAR:
                return DateTime::from($dateTime)->plusYears($amount);

            case self::FOREVER:
                if ($amount > 0) {
                    return DateTime::of(9999, 12, 31, 23, 59, 59, 999999);
                }
                if ($amount < 0) {
                    return DateTime::of(-9999, 1, 1, 0, 0, 0, 0);
                }
                return DateTime::from($dateTime);
        }
    }

    public function between(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): int
    {
        switch ($this->name) {
            case self::MICRO:
                return $this->diffMicros($startInclusive, $endExclusive);

            case self::MILLI:
                return intdiv($this->diffMicros($startInclusive, $endExclusive), 1000);

            case self::SECOND:
                return $this->diffSeconds($startInclusive, $endExclusive);

            case self::MINUTE:
                return intdiv($this->diffSeconds($startInclusive, $endExclusive), DateTime::SECONDS_PER_MINUTE);

            case self::HOUR:
                return intdiv($this->diffSeconds($startInclusive, $endExclusive), DateTime::SECONDS_PER_HOUR);

            case self::DAY:
                return intdiv($this->diffSeconds($startInclusive, $endExclusive), DateTime::SECONDS_PER_DAY);

            case self::WEEK:
                return intdiv($this->diffSeconds($startInclusive, $endExclusive), DateTime::SECONDS_PER_DAY * DateTime::DAYS_PER_WEEK);

            case self::MONTH:
                return $this->diffMonths($startInclusive, $endExclusive);

            case self::YEAR:
                return intdiv($this->diffMonths($startInclusive, $endExclusive), DateTime::MONTHS_PER_YEAR);

            case self::FOREVER:
                return 0;
        }
    }

    public function getDuration(): Duration
    {
        return $this->duration;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    private function diffMicros(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): int
    {
        return Duration::between($startInclusive, $endExclusive)->toMicros();
    }

    private function diffSeconds(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): int
    {
        $startInclusive = DateTime::from($startInclusive);
        $endExclusive = DateTime::from($endExclusive);

        $seconds = $endExclusive->getTimestamp() - $startInclusive->getTimestamp();
        if ($seconds != 0) {
            $micros = $endExclusive->getMicro() - $startInclusive->getMicro();
            if ($seconds < 0) {
                if ($micros > 0) {
                    $seconds++;
                }
            } else {
                if ($micros < 0) {
                    $seconds--;
                }
            }
        }

        return $seconds;
    }

    private function diffMonths(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): int
    {
        $dateInterval = $startInclusive->diff($endExclusive);

        $months = $dateInterval->y * DateTime::MONTHS_PER_YEAR
            + $dateInterval->m;

        if ($dateInterval->invert) {
            $months = -$months;
        }

        return $months;
    }
}
