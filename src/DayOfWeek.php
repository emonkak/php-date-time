<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * A day-of-week, such as Tuesday.
 */
final class DayOfWeek
{
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;
    const SUNDAY    = 7;

    /**
     * The ISO-8601 value for the day of the week, from 1 (Monday) to 7 (Sunday).
     *
     * @var int
     */
    private $value;

    /**
     * Returns an instance of DayOfWeek for the given day-of-week value.
     */
    public static function of(int $dayOfWeek): DayOfWeek
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw new DateTimeException('Invalid value for DayOfWeek: ' . $dayOfWeek);
        }

        return self::get($dayOfWeek);
    }

    /**
     * Returns the seven days of the week in an array.
     */
    public static function all(DayOfWeek $first = null): array
    {
        $days = [];
        $first = $first ?: self::get(self::MONDAY);
        $current = $first;

        do {
            $days[] = $current;
            $current = $current->plus(1);
        }
        while (!$current->isEqualTo($first));

        return $days;
    }

    /**
     * Returns a day-of-week instance for Monday.
     */
    public static function monday(): DayOfWeek
    {
        return self::get(self::MONDAY);
    }

    /**
     * Returns a day-of-week instance for Tuesday.
     */
    public static function tuesday(): DayOfWeek
    {
        return self::get(self::TUESDAY);
    }

    /**
     * Returns a day-of-week instance for Wednesday.
     */
    public static function wednesday(): DayOfWeek
    {
        return self::get(self::WEDNESDAY);
    }

    /**
     * Returns a day-of-week instance for Thursday.
     */
    public static function thursday(): DayOfWeek
    {
        return self::get(self::THURSDAY);
    }

    /**
     * Returns a day-of-week instance for Friday.
     */
    public static function friday(): DayOfWeek
    {
        return self::get(self::FRIDAY);
    }

    /**
     * Returns a day-of-week instance for Saturday.
     */
    public static function saturday(): DayOfWeek
    {
        return self::get(self::SATURDAY);
    }

    /**
     * Returns a day-of-week instance for Sunday.
     */
    public static function sunday(): DayOfWeek
    {
        return self::get(self::SUNDAY);
    }

    /**
     * Returns a cached DayOfWeek instance.
     */
    private static function get(int $value): DayOfWeek
    {
        static $values;

        if (!isset($values[$value])) {
            $values[$value] = new DayOfWeek($value);
        }

        return $values[$value];
    }

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * Returns the ISO 8601 value of this DayOfWeek.
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Returns whether this day-of-week matches the given day-of-week value.
     */
    public function is(int $dayOfWeek): bool
    {
        return $this->value === $dayOfWeek;
    }

    /**
     * Returns whether this DayOfWeek equals another DayOfWeek.
     */
    public function isEqualTo(DayOfWeek $that): bool
    {
        return $this->value === $that->value;
    }

    /**
     * Returns the DayOfWeek that is the specified number of days after this one.
     */
    public function plus(int $days): DayOfWeek
    {
        return self::get((((($this->value - 1 + $days) % 7) + 7) % 7) + 1);
    }

    /**
     * Returns the DayOfWeek that is the specified number of days before this one.
     */
    public function minus(int $days): DayOfWeek
    {
        return $this->plus(-$days);
    }

    /**
     * Returns the capitalized English name of this day-of-week.
     */
    public function __toString(): string
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ][$this->value];
    }
}
