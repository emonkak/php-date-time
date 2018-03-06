<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * Represents a time interval.
 */
class Interval
{
    /**
     * The start datetime, inclusive.
     *
     * @var \DateTimeInterface
     */
    private $start;

    /**
     * The end datetime, exclusive.
     *
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * @throws DateTimeException if the end is before the start
     */
    public function __construct(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive)
    {
        if ($endExclusive < $startInclusive) {
            throw new DateTimeException('The end datetime must not be before the start datetime.');
        }

        $this->start = $startInclusive;
        $this->end = $endExclusive;
    }

    /**
     * Returns the start datetime, inclusive, of this Interval.
     */
    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    /**
     * Returns the end datetime, exclusive, of this Interval.
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start datetime altered.
     */
    public function withStart(\DateTimeInterface $start): Interval
    {
        return new Interval($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end datetime altered.
     */
    public function withEnd(\DateTimeInterface $end): Interval
    {
        return new Interval($this->start, $end);
    }

    /**
     * Gets the gap between this interval and another interval.
     */
    public function gap(Interval $other): ?Interval
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        if ($thisStart > $otherEnd) {
            return new Interval($otherEnd, $thisStart);
        } else if ($otherStart > $thisEnd) {
            return new Interval($thisEnd, $otherStart);
        } else {
            return null;
        }
    }

    /**
     * Gets the overlap between this interval and another interval.
     */
    public function overlap(Interval $other): ?Interval
    {
        if (!$this->overlaps($other)) {
            return null;
        }
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        $start = $thisStart > $otherStart ? $thisStart : $otherStart;
        $end = $thisEnd < $otherEnd ? $thisEnd : $otherEnd;
        return new Interval($start, $end);
    }

    /**
     * Gets the covered interval between this Interval and another interval.
     */
    public function cover(Interval $other): Interval
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        $start = $thisStart < $otherStart ? $thisStart : $otherStart;
        $end = $thisEnd > $otherEnd ? $thisEnd : $otherEnd;
        return new Interval($start, $end);
    }

    /**
     * Gets the union between this Interval and another interval.
     */
    public function union(Interval $other): ?Interval
    {
        if (!$this->overlaps($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Joins the interval between the adjacent.
     */
    public function join(Interval $other): ?Interval
    {
        if (!$this->abuts($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Returns a duration representing the time elapsed in this interval.
     */
    public function getDuration(): Duration
    {
        return Duration::between($this->start, $this->end);
    }

    /**
     * Returns whether this interval abut with the interval specified.
     */
    public function abuts(Interval $other): bool
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $otherEnd == $thisStart || $thisEnd == $otherStart;
    }

    /**
     * Returns whether this interval contain the specified interval.
     */
    public function contains(Interval $other): bool
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $thisStart <= $otherStart && $otherStart < $thisEnd && $otherEnd <= $thisEnd;
    }

    /**
     * Returns whether this interval contain the specified instant.
     */
    public function containsInstant(\DateTimeInterface $dateTime): bool
    {
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $dateTime >= $thisStart && $dateTime < $thisEnd;
    }

    /**
     * Checks if this Interval is equal to the specified time.
     */
    public function isEqualTo(Interval $other): bool
    {
        return $this->start->format('U.u') === $other->start->format('U.u')
               && $this->end->format('U.u') === $other->end->format('U.u');
    }

    /**
     * Does this interval overlap the specified interval.
     */
    public function overlaps(Interval $other): bool
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $thisStart < $otherEnd && $otherStart < $thisEnd;
    }

    /**
     * Returns a string in ISO8601 interval format.
     */
    public function __toString(): string
    {
        return $this->start->format(\DateTime::ATOM) . '/' . $this->end->format(\DateTime::ATOM);
    }
}
