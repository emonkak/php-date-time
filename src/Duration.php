<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * Represents a duration of time.
 */
class Duration
{
    const MONTHS_PER_YEAR    = 12;
    const DAYS_PER_WEEK      = 7;
    const HOURS_PER_DAY      = 24;
    const MINUTES_PER_HOUR   = 60;
    const MINUTES_PER_DAY    = 60 * 24;
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_HOUR   = 60 * 60;
    const SECONDS_PER_DAY    = 60 * 60 * 24;
    const NANOS_PER_SECOND   = 1000000000;
    const NANOS_PER_MINUTE   = 1000000000 * 60;
    const NANOS_PER_HOUR     = 1000000000 * 60 * 60;
    const NANOS_PER_DAY      = 1000000000 * 60 * 60 * 24;

    /**
     * The number of seconds in the duration.
     *
     * @var int
     */
    private $seconds;

    /**
     * The number of nanoseconds in the duration, validated as an integer in the range 0 to 999,999,999.
     *
     * @var int
     */
    private $nanos;

    /**
     * Creates a zero length Duration.
     */
    public static function zero(): Duration
    {
        return new Duration(0);
    }

    /**
     * Creates a zero length duration.
     */
    public static function ofSeconds(int $seconds, int $nanoAdjustment = 0): Duration
    {
        $nanoSeconds = $nanoAdjustment % self::NANOS_PER_SECOND;
        $seconds += ($nanoAdjustment - $nanoSeconds) / self::NANOS_PER_SECOND;

        if ($nanoSeconds < 0) {
            $nanoSeconds += self::NANOS_PER_SECOND;
            $seconds--;
        }

        return new Duration($seconds, $nanoSeconds);
    }

    /**
     * Creates a duration from a number of minutes.
     */
    public static function ofMinutes(int $minutes): Duration
    {
        return new Duration(self::SECONDS_PER_MINUTE * $minutes);
    }

    /**
     * Creates a duration from a number of hours.
     */
    public static function ofHours(int $hours): Duration
    {
        return new Duration(self::SECONDS_PER_HOUR * $hours);
    }

    /**
     * Creates a duration from a number of days.
     */
    public static function ofDays(int $days): Duration
    {
        return new Duration(self::SECONDS_PER_DAY * $days);
    }

    /**
     * Creates a duration that representing the time elapsed between two date and time.
     */
    public static function between(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): Duration
    {
        $seconds = $endExclusive->getTimestamp() - $startInclusive->getTimestamp();
        $micros = $endExclusive->format('u') - $startInclusive->format('u');

        return Duration::ofSeconds($seconds, $micros * 1000);
    }

    private function __construct(int $seconds, int $nanos = 0)
    {
        $this->seconds = $seconds;
        $this->nanos   = $nanos;
    }

    /**
     * Gets the number of seconds in this duration.
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Gets the number of nanoseconds within the second in this duration.
     */
    public function getNanos(): int
    {
        return $this->nanos;
    }

    /**
     * Returns a copy of this duration with the specified nano-of-second.
     */
    public function withSeconds(int $seconds): Duration
    {
        return new Duration($seconds, $this->nanos);
    }

    /**
     * Returns a copy of this duration with the specified nano-of-second.
     */
    public function withNanos(int $nanos): Duration
    {
        return Duration::ofSeconds($this->seconds, $nanos);
    }

    /**
     * Returns whether this duration is zero length.
     */
    public function isZero(): bool
    {
        return $this->seconds === 0 && $this->nanos === 0;
    }

    /**
     * Returns whether this duration is positive, excluding zero.
     */
    public function isPositive(): bool
    {
        return $this->seconds > 0 || ($this->seconds === 0 && $this->nanos !== 0);
    }

    /**
     * Returns whether this duration is positive or zero.
     */
    public function isPositiveOrZero(): bool
    {
        return $this->seconds >= 0;
    }

    /**
     * Returns whether this duration is negative, excluding zero.
     */
    public function isNegative(): bool
    {
        return $this->seconds < 0;
    }

    /**
     * Returns whether this duration is negative or zero.
     */
    public function isNegativeOrZero(): bool
    {
        return $this->seconds < 0 || ($this->seconds === 0 && $this->nanos === 0);
    }

    /**
     * Compares this duration to the specified duration.
     */
    public function compareTo(Duration $that): int
    {
        $seconds = $this->seconds - $that->seconds;

        if ($seconds !== 0) {
            return $seconds > 0 ? 1 : -1;
        }

        $nanos = $this->nanos - $that->nanos;

        if ($nanos !== 0) {
            return $nanos > 0 ? 1 : -1;
        }

        return 0;
    }

    /**
     * Returns whether this Duration is equal to the specified duration.
     */
    public function isEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this Duration is greater than the specified duration.
     */
    public function isGreaterThan(Duration $that): bool
    {
        return $this->compareTo($that) > 0;
    }

    /**
     * Returns whether this Duration is less than the specified duration.
     */
    public function isLessThan(Duration $that): bool
    {
        return $this->compareTo($that) < 0;
    }

    /**
     * Returns whether this Duration is greater than or equal to the specified duration.
     */
    public function isGreaterThanOrEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns whether this Duration is less than or equal to the specified duration.
     */
    public function isLessThanOrEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * Returns a copy of this duration with the specified duration added.
     */
    public function plus(Duration $duration): Duration
    {
        if ($duration->isZero()) {
            return $this;
        }

        $seconds = $this->seconds + $duration->seconds;
        $nanos = $this->nanos + $duration->nanos;

        if ($nanos >= self::NANOS_PER_SECOND) {
            $nanos -= self::NANOS_PER_SECOND;
            $seconds++;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this duration with the specified duration in seconds added.
     */
    public function plusSeconds(int $seconds): Duration
    {
        if ($seconds === 0) {
            return $this;
        }

        return new Duration($this->seconds + $seconds, $this->nanos);
    }

    /**
     * Returns a copy of this duration with the specified duration in minutes added.
     */
    public function plusMinutes(int $minutes): Duration
    {
        return $this->plusSeconds($minutes * self::SECONDS_PER_MINUTE);
    }

    /**
     * Returns a copy of this duration with the specified duration in hours added.
     */
    public function plusHours(int $hours): Duration
    {
        return $this->plusSeconds($hours * self::SECONDS_PER_HOUR);
    }

    /**
     * Returns a copy of this duration with the specified duration in days added.
     */
    public function plusDays(int $days): Duration
    {
        return $this->plusSeconds($days * self::SECONDS_PER_DAY);
    }

    /**
     * Returns a copy of this duration with the specified duration added.
     */
    public function minus(Duration $duration): Duration
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->plus($duration->negated());
    }

    /**
     * Returns a copy of this duration with the specified duration in seconds subtracted.
     */
    public function minusSeconds(int $seconds): Duration
    {
        return $this->plusSeconds(-$seconds);
    }

    /**
     * Returns a copy of this duration with the specified duration in minutes subtracted.
     */
    public function minusMinutes(int $minutes): Duration
    {
        return $this->plusMinutes(-$minutes);
    }

    /**
     * Returns a copy of this duration with the specified duration in hours subtracted.
     */
    public function minusHours(int $hours): Duration
    {
        return $this->plusHours(-$hours);
    }

    /**
     * Returns a copy of this duration with the specified duration in days subtracted.
     */
    public function minusDays(int $days): Duration
    {
        return $this->plusDays(-$days);
    }

    /**
     * Returns a copy of this duration multiplied by the scalar.
     */
    public function multipliedBy(int $multiplicand): Duration
    {
        if ($multiplicand === 0) {
            return Duration::zero();
        }

        if ($multiplicand === 1) {
            return $this;
        }

        $seconds = $this->seconds * $multiplicand;
        $totalnanos = $this->nanos * $multiplicand;

        return Duration::ofSeconds($seconds, $totalnanos);
    }

    /**
     * Returns a copy of this duration divided by the specified value.
     *
     * @throws DateTimeException If the divisor is zero.
     */
    public function dividedBy(int $divisor): Duration
    {
        if ($divisor === 0) {
            throw new DateTimeException('Cannot divide a Duration by zero.');
        }

        if ($divisor === 1) {
            return $this;
        }

        $seconds = $this->seconds;
        $nanos = $this->nanos;

        if ($seconds < 0 && $nanos !== 0) {
            $seconds++;
            $nanos -= self::NANOS_PER_SECOND;
        }

        $remainder = $seconds % $divisor;
        $seconds = intdiv($seconds, $divisor);

        $r1 = $nanos % $divisor;
        $nanos = intdiv($nanos, $divisor);

        $r2 = self::NANOS_PER_SECOND % $divisor;
        $nanos += $remainder * intdiv(self::NANOS_PER_SECOND, $divisor);
        $nanos += intdiv($r1 + $remainder * $r2, $divisor);

        if ($nanos < 0) {
            $seconds--;
            $nanos = self::NANOS_PER_SECOND + $nanos;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this duration with the length negated.
     */
    public function negated(): Duration
    {
        if ($this->isZero()) {
            return $this;
        }

        $seconds = -$this->seconds;
        $nanos = $this->nanos;

        if ($nanos !== 0) {
            $nanos = self::NANOS_PER_SECOND - $nanos;
            $seconds--;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this duration with a positive length.
     */
    public function abs(): Duration
    {
        return $this->isNegative() ? $this->negated() : $this;
    }

    /**
     * Returns a copy of this duration truncated to the specified unit.
     */
    public function truncatedTo(Duration $unit): Duration
    {
        if ($unit->seconds > self::SECONDS_PER_DAY) {
            throw new DateTimeException('Unit is too large to be used for truncation.');
        }

        $unitNanos = $unit->toTotalNanos();
        if ((self::NANOS_PER_DAY % $unitNanos) !== 0) {
            throw new DateTimeException('Unit must divide into a standard day without remainder.');
        }

        $nanoOfDay = ($this->seconds % self::SECONDS_PER_DAY) * self::NANOS_PER_SECOND + $this->nanos;
        $result = intdiv($nanoOfDay, $unitNanos) * $unitNanos;

        return Duration::ofSeconds($this->seconds, $this->nanos + ($result - $nanoOfDay));
    }

    /**
     * Converts the number of days in this duration.
     */
    public function toDays(): int
    {
        return intdiv($this->seconds, self::SECONDS_PER_DAY);
    }

    /**
     * Converts the number of hours in this duration.
     */
    public function toHours(): int
    {
        return intdiv($this->seconds, self::SECONDS_PER_HOUR);
    }

    /**
     * Converts the number of minutes in this duration.
     */
    public function toMinutes(): int
    {
        return intdiv($this->seconds, self::SECONDS_PER_MINUTE);
    }

    /**
     * Converts this duration to the total length in milliseconds.
     */
    public function toTotalMillis(): int
    {
        $millis = $this->seconds * 1000;
        $millis += intdiv($this->nanos, 1000000);

        return $millis;
    }

    /**
     * Converts this duration to the total length in microseconds.
     */
    public function toTotalMicros(): int
    {
        $micros = $this->seconds * 1000000;
        $micros += intdiv($this->nanos, 1000);

        return $micros;
    }

    /**
     * Converts this duration to the total length in nanoseconds.
     */
    public function toTotalNanos(): int
    {
        $nanos = $this->seconds * 1000000000;
        $nanos += $this->nanos;

        return $nanos;
    }

    /**
     * Converts this duration to an ISO-8601 string representation.
     */
    public function __toString(): string
    {
        $seconds = $this->seconds;
        $nanos = $this->nanos;

        if ($seconds === 0 && $nanos === 0) {
            return 'PT0S';
        }

        $negative = ($seconds < 0);

        if ($seconds < 0 && $nanos !== 0) {
            $seconds++;
            $nanos = self::NANOS_PER_SECOND - $nanos;
        }

        $hours = intdiv($seconds, self::SECONDS_PER_HOUR);
        $minutes = intdiv($seconds % self::SECONDS_PER_HOUR, self::SECONDS_PER_MINUTE);
        $seconds = $seconds % self::SECONDS_PER_MINUTE;

        $string = 'PT';

        if ($hours !== 0) {
            $string .= $hours . 'H';
        }
        if ($minutes !== 0) {
            $string .= $minutes . 'M';
        }

        if ($seconds !== 0 || $nanos !== 0) {
            $string .= (($seconds === 0 && $negative) ? '-0' : $seconds);

            if ($nanos !== 0) {
                $string .= '.' . rtrim(sprintf('%09d', $nanos), '0');
            }

            $string .= 'S';
        }

        return $string;
    }
}
