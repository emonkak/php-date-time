<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * Represents a duration of time.
 */
class Duration
{
    /**
     * The number of seconds in the duration.
     *
     * @var int
     */
    private $seconds;

    /**
     * The number of microseconds in the duration, validated as an integer in the range 0 to 999,999.
     *
     * @var int
     */
    private $micros;

    /**
     * Creates a zero length duration.
     */
    public static function zero(): Duration
    {
        return new Duration(0, 0);
    }

    /**
     * Creates a duration from a number of days.
     */
    public static function ofDays(int $days): Duration
    {
        return new Duration(DateTime::SECONDS_PER_DAY * $days, 0);
    }

    /**
     * Creates a duration from a number of hours.
     */
    public static function ofHours(int $hours): Duration
    {
        return new Duration(DateTime::SECONDS_PER_HOUR * $hours, 0);
    }

    /**
     * Creates a duration from a number of minutes.
     */
    public static function ofMinutes(int $minutes): Duration
    {
        return new Duration(DateTime::SECONDS_PER_MINUTE * $minutes, 0);
    }

    /**
     * Creates a duration representing a number of seconds and an adjustment in microseconds.
     */
    public static function ofSeconds(int $seconds, int $microAdjustment = 0): Duration
    {
        $micros = $microAdjustment % DateTime::MICROS_PER_SECOND;
        $seconds += intdiv($microAdjustment, DateTime::MICROS_PER_SECOND);

        if ($micros < 0) {
            $micros += DateTime::MICROS_PER_SECOND;
            $seconds--;
        }

        return new Duration($seconds, $micros);
    }

    /**
     * Creates a duration from a number of microseconds.
     */
    public static function ofMicros(int $micros): Duration
    {
        return self::ofSeconds(0, $micros);
    }

    /**
     * Creates a duration that representing the time elapsed between two date and time.
     */
    public static function between(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive): Duration
    {
        $seconds = $endExclusive->getTimestamp() - $startInclusive->getTimestamp();
        $micros = $endExclusive->format('u') - $startInclusive->format('u');

        return self::ofSeconds($seconds, $micros);
    }

    private function __construct(int $seconds, int $micros)
    {
        $this->seconds = $seconds;
        $this->micros = $micros;
    }

    /**
     * Gets the number of seconds in this duration.
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Gets the number of microseconds within the second in this duration.
     */
    public function getMicros(): int
    {
        return $this->micros;
    }

    /**
     * Returns a copy of this duration with the specified micro-of-second.
     */
    public function withSeconds(int $seconds): Duration
    {
        return new Duration($seconds, $this->micros);
    }

    /**
     * Returns a copy of this duration with the specified micro-of-second.
     */
    public function withMicros(int $micros): Duration
    {
        return self::ofSeconds($this->seconds, $micros);
    }

    /**
     * Returns whether this duration is zero length.
     */
    public function isZero(): bool
    {
        return $this->seconds === 0 && $this->micros === 0;
    }

    /**
     * Returns whether this duration is positive, excluding zero.
     */
    public function isPositive(): bool
    {
        return $this->seconds > 0 || ($this->seconds === 0 && $this->micros !== 0);
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
        return $this->seconds < 0 || ($this->seconds === 0 && $this->micros === 0);
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

        $micros = $this->micros - $that->micros;

        if ($micros !== 0) {
            return $micros > 0 ? 1 : -1;
        }

        return 0;
    }

    /**
     * Returns whether this duration is equal to the specified duration.
     */
    public function isEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this duration is greater than the specified duration.
     */
    public function isGreaterThan(Duration $that): bool
    {
        return $this->compareTo($that) > 0;
    }

    /**
     * Returns whether this duration is less than the specified duration.
     */
    public function isLessThan(Duration $that): bool
    {
        return $this->compareTo($that) < 0;
    }

    /**
     * Returns whether this duration is greater than or equal to the specified duration.
     */
    public function isGreaterThanOrEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns whether this duration is less than or equal to the specified duration.
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
        $micros = $this->micros + $duration->micros;

        if ($micros >= DateTime::MICROS_PER_SECOND) {
            $micros -= DateTime::MICROS_PER_SECOND;
            $seconds++;
        }

        return new Duration($seconds, $micros);
    }

    /**
     * Returns a copy of this duration with the specified duration in days added.
     */
    public function plusDays(int $days): Duration
    {
        return $this->plusSeconds($days * DateTime::SECONDS_PER_DAY);
    }

    /**
     * Returns a copy of this duration with the specified duration in hours added.
     */
    public function plusHours(int $hours): Duration
    {
        return $this->plusSeconds($hours * DateTime::SECONDS_PER_HOUR);
    }

    /**
     * Returns a copy of this duration with the specified duration in minutes added.
     */
    public function plusMinutes(int $minutes): Duration
    {
        return $this->plusSeconds($minutes * DateTime::SECONDS_PER_MINUTE);
    }

    /**
     * Returns a copy of this duration with the specified duration in seconds added.
     */
    public function plusSeconds(int $seconds): Duration
    {
        if ($seconds === 0) {
            return $this;
        }

        return new Duration($this->seconds + $seconds, $this->micros);
    }

    /**
     * Returns a copy of this duration with the specified duration in microseconds added.
     */
    public function plusMicros(int $micros): Duration
    {
        if ($micros === 0) {
            return $this;
        }

        return self::ofSeconds($this->seconds, $this->micros + $micros);
    }

    /**
     * Returns a copy of this duration with the specified duration added.
     */
    public function minus(Duration $duration): Duration
    {
        return $this->plus($duration->negated());
    }

    /**
     * Returns a copy of this duration with the specified duration in days subtracted.
     */
    public function minusDays(int $days): Duration
    {
        return $this->plusDays(-$days);
    }

    /**
     * Returns a copy of this duration with the specified duration in hours subtracted.
     */
    public function minusHours(int $hours): Duration
    {
        return $this->plusHours(-$hours);
    }

    /**
     * Returns a copy of this duration with the specified duration in minutes subtracted.
     */
    public function minusMinutes(int $minutes): Duration
    {
        return $this->plusMinutes(-$minutes);
    }

    /**
     * Returns a copy of this duration with the specified duration in seconds subtracted.
     */
    public function minusSeconds(int $seconds): Duration
    {
        return $this->plusSeconds(-$seconds);
    }

    /**
     * Returns a copy of this duration with the specified duration in microseconds subtracted.
     */
    public function minusMicros(int $micros): Duration
    {
        return $this->plusMicros(-$micros);
    }

    /**
     * Returns a copy of this duration multiplied by the scalar.
     */
    public function multipliedBy(int $multiplicand): Duration
    {
        if ($multiplicand === 0) {
            return self::zero();
        }

        if ($multiplicand === 1) {
            return $this;
        }

        $seconds = $this->seconds * $multiplicand;
        $totalmicros = $this->micros * $multiplicand;

        return self::ofSeconds($seconds, $totalmicros);
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
        $micros = $this->micros;

        if ($seconds < 0 && $micros !== 0) {
            $seconds++;
            $micros -= DateTime::MICROS_PER_SECOND;
        }

        $remainder = $seconds % $divisor;
        $seconds = intdiv($seconds, $divisor);

        $r1 = $micros % $divisor;
        $micros = intdiv($micros, $divisor);

        $r2 = DateTime::MICROS_PER_SECOND % $divisor;
        $micros += $remainder * intdiv(DateTime::MICROS_PER_SECOND, $divisor);
        $micros += intdiv($r1 + $remainder * $r2, $divisor);

        if ($micros < 0) {
            $seconds--;
            $micros = DateTime::MICROS_PER_SECOND + $micros;
        }

        return new Duration($seconds, $micros);
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
        $micros = $this->micros;

        if ($micros !== 0) {
            $micros = DateTime::MICROS_PER_SECOND - $micros;
            $seconds--;
        }

        return new Duration($seconds, $micros);
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
    public function truncatedTo(UnitInterface $unit): Duration
    {
        $unitDuration = $unit->getDuration();

        if ($unitDuration->seconds > DateTime::SECONDS_PER_DAY) {
            throw new DateTimeException('Unit is too large to be used for truncation.');
        }

        $unitMicros = $unitDuration->toMicros();
        if ($unitMicros === 0 || (DateTime::MICROS_PER_DAY % $unitMicros) !== 0) {
            throw new DateTimeException('Unit must divide into a standard day without remainder.');
        }

        $microOfDay = ($this->seconds % DateTime::SECONDS_PER_DAY) * DateTime::MICROS_PER_SECOND + $this->micros;
        $result = intdiv($microOfDay, $unitMicros) * $unitMicros;

        return self::ofSeconds($this->seconds, $this->micros + ($result - $microOfDay));
    }

    /**
     * Converts this duration to the total length in microseconds.
     */
    public function toMicros(): int
    {
        $seconds = $this->seconds;
        $micros = $this->micros;

        if ($seconds < 0) {
            $seconds = $seconds + 1;
            $micros = $micros - DateTime::MICROS_PER_SECOND;
        }

        return $seconds * DateTime::MICROS_PER_SECOND + $micros;
    }

    /**
     * Converts the number of minutes in this duration.
     */
    public function toMinutes(): int
    {
        return intdiv($this->seconds, DateTime::SECONDS_PER_MINUTE);
    }

    /**
     * Converts the number of hours in this duration.
     */
    public function toHours(): int
    {
        return intdiv($this->seconds, DateTime::SECONDS_PER_HOUR);
    }

    /**
     * Converts the number of days in this duration.
     */
    public function toDays(): int
    {
        return intdiv($this->seconds, DateTime::SECONDS_PER_DAY);
    }

    /**
     * Converts this duration to an ISO-8601 string representation.
     */
    public function __toString(): string
    {
        $seconds = $this->seconds;
        $micros = $this->micros;

        if ($seconds === 0 && $micros === 0) {
            return 'PT0S';
        }

        $negative = ($seconds < 0);

        if ($seconds < 0 && $micros !== 0) {
            $seconds++;
            $micros = DateTime::MICROS_PER_SECOND - $micros;
        }

        $hours = intdiv($seconds, DateTime::SECONDS_PER_HOUR);
        $minutes = intdiv($seconds % DateTime::SECONDS_PER_HOUR, DateTime::SECONDS_PER_MINUTE);
        $seconds = $seconds % DateTime::SECONDS_PER_MINUTE;

        $string = 'PT';

        if ($hours !== 0) {
            $string .= $hours . 'H';
        }
        if ($minutes !== 0) {
            $string .= $minutes . 'M';
        }

        if ($seconds !== 0 || $micros !== 0) {
            $string .= (($seconds === 0 && $negative) ? '-0' : $seconds);

            if ($micros !== 0) {
                $string .= '.' . rtrim(sprintf('%06d', $micros), '0');
            }

            $string .= 'S';
        }

        return $string;
    }
}
