<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

/**
 * Represents a time interval.
 *
 * @template TDateTime of \DateTimeInterface
 */
class Interval
{
    /**
     * The start datetime, inclusive.
     *
     * @var TDateTime
     */
    private $start;

    /**
     * The end datetime, exclusive.
     *
     * @var TDateTime
     */
    private $end;

    /**
     * @throws DateTimeException if the end is before the start
     *
     * @param TDateTime $startInclusive
     * @param TDateTime $endExclusive
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
     *
     * @return TDateTime
     */
    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    /**
     * Returns the end datetime, exclusive, of this Interval.
     *
     * @return TDateTime
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start datetime altered.
     *
     * @param TDateTime $start
     * @return self<TDateTime>
     */
    public function withStart(\DateTimeInterface $start): self
    {
        return new self($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end datetime altered.
     *
     * @param TDateTime $end
     * @return self<TDateTime>
     */
    public function withEnd(\DateTimeInterface $end): self
    {
        return new self($this->start, $end);
    }

    /**
     * Gets the gap between this interval and another interval.
     *
     * @param self<TDateTime> $other
     * @return self<TDateTime>
     */
    public function gap(self $other): ?self
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        if ($thisStart > $otherEnd) {
            return new self($otherEnd, $thisStart);
        } elseif ($otherStart > $thisEnd) {
            return new self($thisEnd, $otherStart);
        } else {
            return null;
        }
    }

    /**
     * Gets the overlap between this interval and another interval.
     */
    public function overlap(self $other): ?self
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
        return new self($start, $end);
    }

    /**
     * Gets the covered interval between this Interval and another interval.
     */
    public function cover(self $other): self
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        $start = $thisStart < $otherStart ? $thisStart : $otherStart;
        $end = $thisEnd > $otherEnd ? $thisEnd : $otherEnd;
        return new self($start, $end);
    }

    /**
     * Gets the union between this Interval and another interval.
     */
    public function union(self $other): ?self
    {
        if (!$this->overlaps($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Joins the interval between the adjacent.
     */
    public function join(self $other): ?self
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
    public function abuts(self $other): bool
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
    public function contains(self $other): bool
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $thisStart <= $otherStart && $otherStart < $thisEnd && $otherEnd <= $thisEnd;
    }

    /**
     * Returns whether this interval contain the specified date-time.
     *
     * @param TDateTime $dateTime
     */
    public function containsDateTime(\DateTimeInterface $dateTime): bool
    {
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $dateTime >= $thisStart && $dateTime < $thisEnd;
    }

    /**
     * Checks if this Interval is equal to the specified time.
     */
    public function isEqualTo(self $other): bool
    {
        return $this->start->format('U.u') === $other->start->format('U.u')
               && $this->end->format('U.u') === $other->end->format('U.u');
    }

    /**
     * Does this interval overlap the specified interval.
     */
    public function overlaps(self $other): bool
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
