<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * A unit of date-time, such as Days or Hours.
 */
final class Unit
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
     * @var string
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
        return self::of(self::DAY);
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
                return new Unit($name, Duration::ofSeconds(31556952 / 12));

            case self::YEAR:
                return new Unit($name, Duration::ofSeconds(31556952));

            case self::FOREVER:
                return new Unit($name, Duration::ofSeconds(PHP_INT_MAX, 999999));

            default:
                throw new DateTimeException('Invalid name for Unit: ' . $name);
        }
    }

    private function __construct(string $name, Duration $duration)
    {
        $this->name = $name;
        $this->duration = $duration;
    }

    public function addTo(\DateTimeInterface $dateTime, int $amount): DateTime
    {
        return DateTime::from($dateTime)->plusDuration($this->duration->multipliedBy($amount));
    }

    public function subtractFrom(\DateTimeInterface $dateTime, int $amount): DateTime
    {
        return DateTime::from($dateTime)->minusDuration($this->duration->multipliedBy($amount));
    }

    public function between(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): int
    {
        $diff = Duration::between($startInclusive, $endExclusive);

        return $this->duration->getMicros() === 0
            ? intdiv($diff->getSeconds(), $this->duration->getSeconds())
            : intdiv($diff->toTotalMicros(), $this->duration->toTotalMicros());
    }

    public function getDuration(): Duration
    {
        return $this->duration;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
