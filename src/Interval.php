<?php

namespace Emonkak\Interval;

use Herrera\DateInterval\DateInterval;

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
     * @param \DateTimeInterface $startInclusive The start datetime, inclusive.
     * @param \DateTimeInterface $endExclusive   The end datetime, exclusive.
     */
    public function __construct(\DateTimeInterface $startInclusive, \DateTimeInterface $endExclusive)
    {
        if ($endExclusive < $startInclusive) {
            throw new \InvalidArgumentException('The end datetime must not be before the start datetime.');
        }

        $this->start = $startInclusive;
        $this->end = $endExclusive;
    }

    /**
     * Returns the start datetime, inclusive, of this Interval.
     *
     * @return \DateTimeInterface
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end datetime, exclusive, of this Interval.
     *
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start datetime altered.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return Interval
     */
    public function withStart(\DateTimeInterface $start)
    {
        return new Interval($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end datetime altered.
     *
     * @param \DateTimeInterface $end
     *
     * @return Interval
     */
    public function withEnd(\DateTimeInterface $end)
    {
        return new Interval($this->start, $end);
    }

    /**
     * Gets the gap between this interval and another interval.
     *
     * @param Interval $other
     *
     * @return Interval|null
     */
    public function gap(Interval $other)
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
     *
     * @param Interval $other
     *
     * @return Interval
     */
    public function overlap(Interval $other)
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
     *
     * @param Interval $other
     *
     * @return Interval
     */
    public function cover(Interval $other)
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
     *
     * @param Interval $other
     *
     * @return Interval
     */
    public function union(Interval $other)
    {
        if (!$this->overlaps($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Joins the interval between the adjacent.
     *
     * @param Interval $other
     *
     * @return Interval
     */
    public function join(Interval $other)
    {
        if (!$this->abuts($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Returns a Duration representing the time elapsed in this Interval.
     *
     * @return DateInterval
     */
    public function getDuration()
    {
        return DateInterval::fromSeconds($this->end->getTimestamp() - $this->start->getTimestamp());
    }

    /**
     * Does this interval abut with the interval specified.
     *
     * @param Interval $other
     *
     * @return boolean
     */
    public function abuts(Interval $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $otherEnd == $thisStart || $thisEnd == $otherStart;
    }

    /**
     * Does this interval contain the specified interval.
     *
     * @param Interval $other
     *
     * @return boolean
     */
    public function contains(Interval $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $thisStart <= $otherStart && $otherStart < $thisEnd && $otherEnd <= $thisEnd;
    }

    /**
     * Does this interval contain the specified instant.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return boolean
     */
    public function containsInstant(\DateTimeInterface $dateTime)
    {
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $dateTime >= $thisStart && $dateTime < $thisEnd;
    }

    /**
     * Checks if this Interval is equal to the specified time.
     *
     * @param Interval $other The interval to compare to.
     *
     * @return boolean
     */
    public function isEqualTo(Interval $other)
    {
        return $this->start->format('U.u') === $other->start->format('U.u')
               && $this->end->format('U.u') === $other->end->format('U.u');
    }

    /**
     * Does this interval overlap the specified interval.
     *
     * @param Interval $other
     *
     * @return boolean
     */
    public function overlaps(Interval $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $thisStart < $otherEnd && $otherStart < $thisEnd;
    }

    /**
     * Returns a string in ISO8601 interval format.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->start->format(\DateTime::ATOM) . '/' . $this->end->format(\DateTime::ATOM);
    }
}
