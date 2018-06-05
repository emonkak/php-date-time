<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

class DateTime extends \DateTimeImmutable implements \JsonSerializable
{
    const MONTHS_PER_YEAR    = 12;
    const DAYS_PER_WEEK      = 7;
    const HOURS_PER_DAY      = 24;
    const MINUTES_PER_HOUR   = 60;
    const MINUTES_PER_DAY    = 60 * 24;
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_HOUR   = 60 * 60;
    const SECONDS_PER_DAY    = 60 * 60 * 24;
    const MICROS_PER_SECOND  = 1000 * 1000;
    const MICROS_PER_MINUTE  = 1000 * 1000 * 60;
    const MICROS_PER_HOUR    = 1000 * 1000 * 60 * 60;
    const MICROS_PER_DAY     = 1000 * 1000 * 60 * 60 * 24;

    /**
     * Creates an instance of date-time from year, month, day, hour, minute, second and microsecond.
     *
     * @throws DateTimeException If the value of any field is out of range.
     */
    public static function of(int $year, int $month, int $day, int $hour = 0, int $minute = 0, int $second = 0, int $micro = 0, \DateTimeZone $timeZone = null): DateTime
    {
        Field::check(Field::year(), $year);
        Field::check(Field::monthOfYear(), $month);
        Field::check(Field::dayOfMonth(), $day);
        Field::check(Field::hourOfDay(), $hour);
        Field::check(Field::minuteOfHour(), $minute);
        Field::check(Field::secondOfMinute(), $second);
        Field::check(Field::microOfSecond(), $micro);

        if (!checkdate($month, $day, abs($year))) {
            throw new DateTimeException(sprintf('Invalid day of month: %04d-%02d-%02d', $year, $month, $day));
        }

        $text = sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d.%06d',
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second,
            $micro
        );

        return new DateTime($text, $timeZone);
    }

    /**
     * Creates an instance of date-time using seconds from the epoch of 1970-01-01T00:00:00Z.
     */
    public static function ofEpochSecond(int $epochSecond, int $microAdjustment = 0, \DateTimeZone $timeZone = null): DateTime
    {
        $micros = $microAdjustment % self::MICROS_PER_SECOND;
        $epochSecond += ($microAdjustment - $micros) / self::MICROS_PER_SECOND;

        if ($micros < 0) {
            $micros += self::MICROS_PER_SECOND;
            $epochSecond--;
        }

        $text = date('Y-m-d H:i:s', $epochSecond);

        if ($micros !== 0) {
            $text .= '.' . sprintf('%06d', $micros);
        }

        return new DateTime($text, $timeZone);
    }

    /**
     * Create an instance of date-time from DateTimeInterface.
     */
    public static function from(\DateTimeInterface $dateTime): DateTime
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime;
        }

        return new DateTime($dateTime->format('Y-m-d H:i:s.u'), $dateTime->getTimeZone());
    }

    /**
     * Gets the value of the specified field from this date as an int.
     */
    public function get(FieldInterface $field): int
    {
        return $field->getFrom($this);
    }

    /**
     * Gets the micro-of-second field.
     */
    public function getMicro(): int
    {
        return (int) $this->format('u');
    }

    /**
     * Gets the second-of-minute field.
     */
    public function getSecond(): int
    {
        return (int) $this->format('s');
    }

    /**
     * Gets the minute-of-hour field.
     */
    public function getMinute(): int
    {
        return (int) $this->format('i');
    }

    /**
     * Gets the hour-of-day field.
     */
    public function getHour(): int
    {
        return (int) $this->format('G');
    }

    /**
     * Gets the day-of-week field, which is an enum DayOfWeek.
     */
    public function getDayOfWeek(): DayOfWeek
    {
        return DayOfWeek::of((int) $this->format('N'));
    }

    /**
     * Gets the day-of-month field.
     */
    public function getDayOfMonth(): int
    {
        return (int) $this->format('j');
    }

    /**
     * Gets the day-of-year field.
     */
    public function getDayOfYear(): int
    {
        return (int) $this->format('z') + 1;
    }

    /**
     * Gets the month-of-year field from 1 to 12.
     */
    public function getMonth(): int
    {
        return (int) $this->format('n');
    }

    /**
     * Gets the year field.
     */
    public function getYear(): int
    {
        return (int) $this->format('Y');
    }

    /**
     * Returns whether the year is a leap year.
     */
    public function isLeapYear(): bool
    {
        return (int) $this->format('L') === 1;
    }

    /**
     * Returns a copy of this date-time with the specified field set to a new value.
     */
    public function with(FieldInterface $field, int $newValue): DateTime
    {
        return $field->adjustInto($this, $newValue);
    }

    /**
     * Returns a copy of this date-time with the micro-of-second altered.
     */
    public function withMicro(int $micro): DateTime
    {
        $fields = $this->getDateTimeFields();

        return DateTime::of(
            $fields[Field::YEAR],
            $fields[Field::MONTH_OF_YEAR],
            $fields[Field::DAY_OF_MONTH],
            $fields[Field::HOUR_OF_DAY],
            $fields[Field::MINUTE_OF_HOUR],
            $fields[Field::SECOND_OF_MINUTE],
            $micro
        );
    }

    /**
     * Returns a copy of this date-time with the second-of-minute altered.
     */
    public function withSecond(int $second): DateTime
    {
        $fields = $this->getDateTimeFields();

        return DateTime::of(
            $fields[Field::YEAR],
            $fields[Field::MONTH_OF_YEAR],
            $fields[Field::DAY_OF_MONTH],
            $fields[Field::HOUR_OF_DAY],
            $fields[Field::MINUTE_OF_HOUR],
            $second,
            $fields[Field::MICRO_OF_SECOND]
        );
    }

    /**
     * Returns a copy of this date-time with the minute-of-hour altered.
     */
    public function withMinute(int $minute): DateTime
    {
        $fields = $this->getDateTimeFields();

        return DateTime::of(
            $fields[Field::YEAR],
            $fields[Field::MONTH_OF_YEAR],
            $fields[Field::DAY_OF_MONTH],
            $fields[Field::HOUR_OF_DAY],
            $minute,
            $fields[Field::SECOND_OF_MINUTE],
            $fields[Field::MICRO_OF_SECOND]
        );
    }

    /**
     * Returns a copy of this date-time with the hour-of-day altered.
     */
    public function withHour(int $hour): DateTime
    {
        $fields = $this->getDateTimeFields();

        return DateTime::of(
            $fields[Field::YEAR],
            $fields[Field::MONTH_OF_YEAR],
            $fields[Field::DAY_OF_MONTH],
            $hour,
            $fields[Field::MINUTE_OF_HOUR],
            $fields[Field::SECOND_OF_MINUTE],
            $fields[Field::MICRO_OF_SECOND]
        );
    }

    /**
     * Returns a copy of this date-time with the day-of-month altered.
     */
    public function withDay(int $day): DateTime
    {
        $fields = $this->getDateTimeFields();

        return DateTime::of(
            $fields[Field::YEAR],
            $fields[Field::MONTH_OF_YEAR],
            $day,
            $fields[Field::HOUR_OF_DAY],
            $fields[Field::MINUTE_OF_HOUR],
            $fields[Field::SECOND_OF_MINUTE],
            $fields[Field::MICRO_OF_SECOND]
        );
    }

    /**
     * Returns a copy of this date-time with the month-of-year altered.
     */
    public function withMonth(int $month): DateTime
    {
        Field::check(Field::monthOfYear(), $month);

        $fields = $this->getDateTimeFields();

        list ($year, $month, $day) =
            $this->resolveDate($fields[Field::YEAR], $month, $fields[Field::DAY_OF_MONTH]);

        return DateTime::of(
            $year,
            $month,
            $day,
            $fields[Field::HOUR_OF_DAY],
            $fields[Field::MINUTE_OF_HOUR],
            $fields[Field::SECOND_OF_MINUTE],
            $fields[Field::MICRO_OF_SECOND]
        );
    }

    /**
     * Returns a copy of this date-time with the year altered.
     */
    public function withYear(int $year): DateTime
    {
        Field::check(Field::year(), $year);

        $fields = $this->getDateTimeFields();

        list ($year, $month, $day) =
            $this->resolveDate($year, $fields[Field::MONTH_OF_YEAR], $fields[Field::DAY_OF_MONTH]);

        return DateTime::of(
            $year,
            $month,
            $day,
            $fields[Field::HOUR_OF_DAY],
            $fields[Field::MINUTE_OF_HOUR],
            $fields[Field::SECOND_OF_MINUTE],
            $fields[Field::MICRO_OF_SECOND]
        );
    }

    /**
     * Returns a zoned date-time formed from this date-time and the specified time-zone.
     */
    public function withTimeZone(\DateTimeZone $timeZone): DateTime
    {
        return $this->setTimeZone($timeZone);
    }

    /**
     * Returns a copy of this date-time with the specified amount added.
     */
    public function plus(int $amount, UnitInterface $unit)
    {
        return $unit->addTo($this, $amount);
    }

    /**
     * Returns a copy of this date-time with the specific duration added.
     */
    public function plusDuration(Duration $duration): DateTime
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this
            ->plusSeconds($duration->getSeconds())
            ->plusMicros($duration->getMicros());
    }

    /**
     * Returns a copy of this date-time with the specified period in microseconds added.
     */
    public function plusMicros(int $micros): DateTime
    {
        if ($micros === 0) {
            return $this;
        }

        $micro = $this->getMicro();
        $totalMicros = $micro + $micros;

        $newMicro = $totalMicros % self::MICROS_PER_SECOND;
        $secondsToAdd = intdiv($totalMicros, self::MICROS_PER_SECOND);

        if ($newMicro < 0) {
            $newMicro += self::MICROS_PER_SECOND;
            $secondsToAdd--;
        }

        $dateTime = $this;

        if ($micro !== $newMicro) {
            $dateTime = $dateTime->withMicro($newMicro);
        }

        return $dateTime->plusSeconds($secondsToAdd);
    }

    /**
     * Returns a copy of this date-time with the specified period in seconds added.
     */
    public function plusSeconds(int $seconds): DateTime
    {
        if ($seconds === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('PT' . abs($seconds) . 'S');
        $dateInterval->invert = $seconds < 0 ? 1 : 0;

        return $this->add($dateInterval);
    }

    /**
     * Returns a copy of this date-time with the specified period in minutes added.
     */
    public function plusMinutes(int $minutes): DateTime
    {
        if ($minutes === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('PT' . abs($minutes) . 'M');
        $dateInterval->invert = $minutes < 0 ? 1 : 0;

        return $this->add($dateInterval);
    }

    /**
     * Returns a copy of this date-time with the specified period in hours added.
     */
    public function plusHours(int $hours): DateTime
    {
        if ($hours === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('PT' . abs($hours) . 'H');
        $dateInterval->invert = $hours < 0 ? 1 : 0;

        return $this->add($dateInterval);
    }

    /**
     * Returns a copy of this date-time with the specified period in days added.
     */
    public function plusDays(int $days): DateTime
    {
        if ($days === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('P' . abs($days) . 'D');
        $dateInterval->invert = $days < 0 ? 1 : 0;

        return $this->add($dateInterval);
    }

    /**
     * Returns a copy of this date-time with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): DateTime
    {
        if ($weeks === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('P' . abs($weeks) . 'W');
        $dateInterval->invert = $weeks < 0 ? 1 : 0;

        return $this->add($dateInterval);
    }

    /**
     * Returns a copy of this date-time with the specified period in months added.
     */
    public function plusMonths(int $months): DateTime
    {
        if ($months === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('P' . abs($months) . 'M');
        $dateInterval->invert = $months < 0 ? 1 : 0;

        $dateTime = $this->add($dateInterval);
        $dayOfMonth = $dateTime->getDayOfMonth();

        if ($dayOfMonth !== $this->getDayOfMonth()) {
            $dateTime = $dateTime->sub(new \DateInterval('P' . $dayOfMonth . 'D'));
        }

        return $dateTime;
    }

    /**
     * Returns a copy of this date-time with the specified period in years added.
     */
    public function plusYears(int $years): DateTime
    {
        if ($years === 0) {
            return $this;
        }

        $dateInterval = new \DateInterval('P' . abs($years) . 'Y');
        $dateInterval->invert = $years < 0 ? 1 : 0;

        $dateTime = $this->add($dateInterval);
        $dayOfMonth = $dateTime->getDayOfMonth();

        if ($dayOfMonth !== $this->getDayOfMonth()) {
            $dateTime = $dateTime->sub(new \DateInterval('P' . $dayOfMonth . 'D'));
        }

        return $dateTime;
    }

    /**
     * Returns a copy of this date-time with the specified amount subtracted.
     */
    public function minus(int $amount, UnitInterface $unit): DateTime
    {
        return $unit->addTo($this, -$amount);
    }

    /**
     * Returns a copy of this date-time with the specific duration subtracted.
     */
    public function minusDuration(Duration $duration): DateTime
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this
            ->minusSeconds($duration->getSeconds())
            ->minusMicros($duration->getMicros());
    }

    /**
     * Returns a copy of this date-time with the specified period in microseconds subtracted.
     */
    public function minusMicros(int $micros): DateTime
    {
        return $this->plusMicros(-$micros);
    }

    /**
     * Returns a copy of this date-time with the specified period in seconds subtracted.
     */
    public function minusSeconds(int $seconds): DateTime
    {
        return $this->plusSeconds(-$seconds);
    }

    /**
     * Returns a copy of this date-time with the specified period in minutes subtracted.
     */
    public function minusMinutes(int $minutes): DateTime
    {
        return $this->plusMinutes(-$minutes);
    }

    /**
     * Returns a copy of this date-time with the specified period in hours subtracted.
     */
    public function minusHours(int $hours): DateTime
    {
        return $this->plusHours(-$hours);
    }

    /**
     * Returns a copy of this date-time with the specified period in days subtracted.
     */
    public function minusDays(int $days): DateTime
    {
        return $this->plusDays(-$days);
    }

    /**
     * Returns a copy of this date-time with the specified period in weeks subtracted.
     */
    public function minusWeeks(int $weeks): DateTime
    {
        return $this->plusWeeks(-$weeks);
    }

    /**
     * Returns a copy of this date-time with the specified period in months subtracted.
     */
    public function minusMonths(int $months): DateTime
    {
        return $this->plusMonths(-$months);
    }

    /**
     * Returns a copy of this date-time with the specified period in years subtracted.
     */
    public function minusYears(int $years): DateTime
    {
        return $this->plusYears(-$years);
    }

    /**
     * Calculates the amount of time until another instant in terms of the specified unit.
     */
    public function until(\DateTimeInterface $endExclusive, UnitInterface $unit): int
    {
        return $unit->between($this, $endExclusive);
    }

    /**
     * Converts this date-time to the number of seconds from the epoch of 1970-01-01T00:00:00Z.
     */
    public function toEpochSecond(): int
    {
        return $this->getTimestamp() + $this->getTimeZone()->getOffset($this);
    }

    /**
     * Converts this date-time to a string representation of date.
     */
    public function toDateString(): string
    {
        return $this->format('Y-m-d');
    }

    /**
     * Converts this date-time to a string representation of date and time.
     */
    public function toDateTimeString(): string
    {
        list ($date, $time, $micros) =
            explode(' ', $this->format('Y-m-d H:i:s u'));

        return $date . ' ' . $time . ($micros != 0 ? rtrim('.' . $micros, '0') : '');
    }

    /**
     * Converts this date-time to a string representation of time.
     */
    public function toTimeString(): string
    {
        list ($time, $micros) = explode(' ', $this->format('H:i:s u'));

        return $time . ($micros != 0 ? rtrim('.' . $micros, '0') : '');
    }

    /**
     * Converts this date-time to an ISO-8601 string representation.
     */
    public function __toString(): string
    {
        list ($dateTime, $micros, $timeZone) =
            explode(' ', $this->format('Y-m-d\TH:i:s u P'));

        $micros = $micros != 0 ? rtrim('.' . $micros, '0') : '';
        $timeZone = $timeZone === '+00:00' ? 'Z' : $timeZone;

        return $dateTime . $micros . $timeZone;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    private function getDateTimeFields(): array
    {
        list ($year, $month, $day, $hour, $minute, $second, $micro) =
            explode(' ', $this->format('Y n j G i s u'));

        return [
            Field::YEAR => (int) $year,
            Field::MONTH_OF_YEAR => (int) $month,
            Field::DAY_OF_MONTH => (int) $day,
            Field::HOUR_OF_DAY => (int) $hour,
            Field::MINUTE_OF_HOUR => (int) $minute,
            Field::SECOND_OF_MINUTE => (int) $second,
            Field::MICRO_OF_SECOND => (int) $micro
        ];
    }

    private function resolveDate(int $year, int $month, int $day): array
    {
        if ($day > 28) {
            $day = min($day, (int) date('t', mktime(0, 0, 0, $month, 1, $year)));
        }

        return [$year, $month, $day];
    }
}
