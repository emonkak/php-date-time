<?php

namespace Emonkak\TimeSpan;

use Herrera\DateInterval\DateInterval;

/**
 * Represents a time interval.
 */
class TimeSpan
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
     * Returns the start datetime, inclusive, of this TimeSpan.
     *
     * @return \DateTimeInterface
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end datetime, exclusive, of this TimeSpan.
     *
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns a copy of this TimeSpan with the start datetime altered.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return TimeSpan
     */
    public function withStart(\DateTimeInterface $start)
    {
        return new TimeSpan($start, $this->end);
    }

    /**
     * Returns a copy of this TimeSpan with the end datetime altered.
     *
     * @param \DateTimeInterface $end
     *
     * @return TimeSpan
     */
    public function withEnd(\DateTimeInterface $end)
    {
        return new TimeSpan($this->start, $end);
    }

    /**
     * Gets the gap between this time span and another time span.
     *
     * @param TimeSpan $other
     *
     * @return TimeSpan|null
     */
    public function gap(TimeSpan $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        if ($thisStart > $otherEnd) {
            return new TimeSpan($otherEnd, $thisStart);
        } else if ($otherStart > $thisEnd) {
            return new TimeSpan($thisEnd, $otherStart);
        } else {
            return null;
        }
    }

    /**
     * Gets the overlap between this time span and another time span.
     *
     * @param TimeSpan $other
     *
     * @return TimeSpan
     */
    public function overlap(TimeSpan $other)
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
        return new TimeSpan($start, $end);
    }

    /**
     * Gets the covered time span between this TimeSpan and another time span.
     *
     * @param TimeSpan $other
     *
     * @return TimeSpan
     */
    public function cover(TimeSpan $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        $start = $thisStart < $otherStart ? $thisStart : $otherStart;
        $end = $thisEnd > $otherEnd ? $thisEnd : $otherEnd;
        return new TimeSpan($start, $end);
    }

    /**
     * Gets the union between this TimeSpan and another time span.
     *
     * @param TimeSpan $timeSpan
     *
     * @return TimeSpan
     */
    public function union(TimeSpan $other)
    {
        if (!$this->overlaps($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Joins the time span between the adjacent.
     *
     * @param TimeSpan $other
     *
     * @return TimeSpan
     */
    public function join(TimeSpan $other)
    {
        if (!$this->abuts($other)) {
            return null;
        }
        return $this->cover($other);
    }

    /**
     * Returns a Duration representing the time elapsed in this TimeSpan.
     *
     * @return DateInterval
     */
    public function getDuration()
    {
        return DateInterval::fromSeconds($this->end->getTimestamp() - $this->start->getTimestamp());
    }

    /**
     * Does this time span abut with the time span specified.
     *
     * @param TimeSpan $other
     *
     * @return boolean
     */
    public function abuts(TimeSpan $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $otherEnd == $thisStart || $thisEnd == $otherStart;
    }

    /**
     * Does this time span contain the specified time span.
     *
     * @param TimeSpan $other
     *
     * @return boolean
     */
    public function contains(TimeSpan $other)
    {
        $otherStart = $other->start;
        $otherEnd = $other->end;
        $thisStart = $this->start;
        $thisEnd = $this->end;
        return $thisStart <= $otherStart && $otherStart < $thisEnd && $otherEnd <= $thisEnd;
    }

    /**
     * Does this time span contain the specified instant.
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
     * Checks if this TimeSpan is equal to the specified time.
     *
     * @param TimeSpan $other The span to compare to.
     *
     * @return boolean
     */
    public function isEqualTo(TimeSpan $other)
    {
        return $this->start->format('U.u') === $other->start->format('U.u')
               && $this->end->format('U.u') === $other->end->format('U.u');
    }

    /**
     * Does this time span overlap the specified time span.
     *
     * @param TimeSpan $other
     *
     * @return boolean
     */
    public function overlaps(TimeSpan $other)
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
        $timeZone = new \DateTimeZone('UTC');
        $start = \DateTimeImmutable::createFromFormat('U.u', $this->start->format('U.u'))
            ->setTimeZone($timeZone);
        $end = \DateTimeImmutable::createFromFormat('U.u', $this->end->format('U.u'))
            ->setTimeZone($timeZone);
        $format = 'Y-m-d\TH:i:s.u\Z';
        return $start->format($format) . '/' . $end->format($format);
    }
}
