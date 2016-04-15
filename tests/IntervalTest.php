<?php

namespace Emonkak\Interval\Tests;

use Emonkak\Interval\Interval;
use Herrera\DateInterval\DateInterval;

/**
 * @covers Emonkak\Interval\Interval
 */
class IntervalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerConstructorThrowsInvalidArgumentException
     * @expectedException \InvalidArgumentException
     *
     * @param integer $s1 The 1st interval's start second.
     * @param integer $n1 The 1st interval's start micro second.
     * @param integer $s2 The 1st interval's end second.
     * @param integer $n2 The 1st interval's end micro second.
     */
    public function testConstructorThrowsInvalidArgumentException($s1, $n1, $s2, $n2)
    {
        new Interval(new \DateTimeImmutable('1970-01-01 00:00:01'), new \DateTimeImmutable('1970-01-01 00:00:00'));
    }

    /**
     * @return array
     */
    public function providerConstructorThrowsInvalidArgumentException()
    {
        return [
            [1, 0, 0, 0],
            [0, 1, 0, 0],
        ];
    }

    /**
     * @dataProvider providerWithStart
     *
     * @param integer $s The new start second.
     * @param integer $n The new start micro second.
     */
    public function testWithStart($s, $n)
    {
        $timeSpan = new Interval($this->createDateTime(0), $this->createDateTime(100));
        $this->assertIntervalIs($s, $n, 100, 0, $timeSpan->withStart($this->createDateTime($s, $n)));
    }

    /**
     * @return array
     */
    public function providerWithStart()
    {
        return [
            [12, 34],
            [0, 34],
            [12, 0],
            [0, 0]
        ];
    }

    /**
     * @dataProvider providerWithEnd
     *
     * @param integer $s The new end second.
     * @param integer $n The new end micro second.
     */
    public function testWithEnd($s, $n)
    {
        $timeSpan = new Interval($this->createDateTime(0), $this->createDateTime(100));
        $this->assertIntervalIs(0, 0, $s, $n, $timeSpan->withEnd($this->createDateTime($s, $n)));
    }

    /**
     * @return array
     */
    public function providerWithEnd()
    {
        return [
            [12, 34],
            [0, 34],
            [12, 0],
            [0, 0]
        ];
    }

    /**
     * @dataProvider providerGetDuration
     *
     * @param integer $s1 The 1st interval's start second.
     * @param integer $n1 The 1st interval's start micro second.
     * @param integer $s2 The 1st interval's end second.
     * @param integer $n2 The 1st interval's end micro second.
     * @param integer $expected The expected duration in seconds.
     */
    public function testGetDuration($s1, $n1, $s2, $n2, $expected)
    {
        $timeSpan = new Interval($this->createDateTime($s1, $n1), $this->createDateTime($s2, $n2));

        $this->assertSame($expected, (int) $timeSpan->getDuration()->toSeconds());
    }

    /**
     * @return array
     */
    public function providerGetDuration()
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1],
            [1, 0, 2, 0, 1]
        ];
    }

    /**
     * @dataProvider providerGap
     *
     * @param array      $first    The 1st interval's start and end pair.
     * @param array      $second   The 1st interval's start and end pair.
     * @param array|null $expected The expected interval's start and end pair.
     */
    public function testGap(array $first, array $second, $expected)
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        if ($expected !== null) {
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->gap($secondInterval));
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->gap($firstInterval));
        } else {
            $this->assertNull($firstInterval->gap($secondInterval));
            $this->assertNull($secondInterval->gap($firstInterval));
        }
    }

    /**
     * @return array
     */
    public function providerGap()
    {
        return [
            [[3, 7], [0, 1], [1, 3]],
            [[3, 7], [1, 1], [1, 3]],
            [[3, 7], [2, 3], null],  // abuts before
            [[3, 7], [3, 3], null],  // abuts before
            [[3, 7], [4, 6], null],  // overlaps
            [[3, 7], [3, 7], null],  // overlaps
            [[3, 7], [6, 7], null],  // overlaps
            [[3, 7], [7, 7], null],  // abuts before
            [[3, 7], [6, 8], null],  // overlaps
            [[3, 7], [7, 8], null],  // abuts after
            [[3, 7], [8, 8], [7, 8]],
            [[3, 7], [6, 9], null],  // overlaps
            [[3, 7], [7, 9], null],  // abuts after
            [[3, 7], [8, 9], [7, 8]],
            [[3, 7], [9, 9], [7, 9]]
        ];
    }

    /**
     * @dataProvider providerOverlap
     *
     * @param array      $first    The 1st interval's start and end pair.
     * @param array      $second   The 1st interval's start and end pair.
     * @param array|null $expected The expected interval's start and end pair.
     */
    public function testOverlap(array $first, array $second, $expected)
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        if ($expected !== null) {
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->overlap($secondInterval));
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->overlap($firstInterval));
        } else {
            $this->assertNull($firstInterval->overlap($secondInterval));
            $this->assertNull($secondInterval->overlap($firstInterval));
        }
    }

    /**
     * @return array
     */
    public function providerOverlap()
    {
        return [
            [[3, 7], [1, 2],   null],  // gap before
            [[3, 7], [2, 2],   null],  // gap before

            [[3, 7], [2, 3],   null],  // abuts before
            [[3, 7], [3, 3],   null],  // abuts before

            [[3, 7], [2, 4], [3, 4]],  // truncated start
            [[3, 7], [3, 4], [3, 4]],
            [[3, 7], [4, 4], [4, 4]],

            [[3, 7], [2, 7], [3, 7]],  // truncated start
            [[3, 7], [3, 7], [3, 7]],
            [[3, 7], [4, 7], [4, 7]],
            [[3, 7], [5, 7], [5, 7]],
            [[3, 7], [6, 7], [6, 7]],
            [[3, 7], [7, 7],   null],  // abuts after

            [[3, 7], [2, 8], [3, 7]],  // truncated start and end
            [[3, 7], [3, 8], [3, 7]],  // truncated end
            [[3, 7], [4, 8], [4, 7]],  // truncated end
            [[3, 7], [5, 8], [5, 7]],  // truncated end
            [[3, 7], [6, 8], [6, 7]],  // truncated end
            [[3, 7], [7, 8],   null],  // abuts after
            [[3, 7], [8, 8],   null],  // gap after
        ];
    }

    /**
     * @dataProvider providerCover
     *
     * @param array      $first    The 1st interval's start and end pair.
     * @param array      $second   The 1st interval's start and end pair.
     * @param array|null $expected The expected interval's start and end pair.
     */
    public function testCover(array $first, array $second, $expected)
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->cover($secondInterval));
        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->cover($firstInterval));
    }

    /**
     * @return array
     */
    public function providerCover()
    {
        return [
            [[3, 7], [1, 2], [1, 7]],  // gap before
            [[3, 7], [2, 2], [2, 7]],  // gap before

            [[3, 7], [2, 3], [2, 7]],  // abuts before
            [[3, 7], [3, 3], [3, 7]],  // abuts before

            [[3, 7], [2, 4], [2, 7]],  // truncated start
            [[3, 7], [3, 4], [3, 7]],
            [[3, 7], [4, 4], [3, 7]],

            [[3, 7], [2, 7], [2, 7]],  // truncated start
            [[3, 7], [3, 7], [3, 7]],
            [[3, 7], [4, 7], [3, 7]],
            [[3, 7], [5, 7], [3, 7]],
            [[3, 7], [6, 7], [3, 7]],
            [[3, 7], [7, 7], [3, 7]],  // abuts after

            [[3, 7], [2, 8], [2, 8]],  // truncated start and end
            [[3, 7], [3, 8], [3, 8]],  // truncated end
            [[3, 7], [4, 8], [3, 8]],  // truncated end
            [[3, 7], [5, 8], [3, 8]],  // truncated end
            [[3, 7], [6, 8], [3, 8]],  // truncated end
            [[3, 7], [7, 8], [3, 8]],  // abuts after
            [[3, 7], [8, 8], [3, 8]]   // gap after
        ];
    }

    /**
     * @dataProvider providerUnion
     *
     * @param array      $first    The 1st interval's start and end pair.
     * @param array      $second   The 1st interval's start and end pair.
     * @param array|null $expected The expected interval's start and end pair.
     */
    public function testUnion(array $first, array $second, $expected)
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        if ($expected !== null) {
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->union($secondInterval));
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->union($firstInterval));
        } else {
            $this->assertNull($firstInterval->union($secondInterval));
            $this->assertNull($secondInterval->union($firstInterval));
        }
    }

    /**
     * @return array
     */
    public function providerUnion()
    {
        return [
            [[3, 7], [1, 2],   null],  // gap before
            [[3, 7], [2, 2],   null],  // gap before

            [[3, 7], [2, 3],   null],  // abuts before
            [[3, 7], [3, 3],   null],  // abuts before

            [[3, 7], [2, 4], [2, 7]],  // truncated start
            [[3, 7], [3, 4], [3, 7]],
            [[3, 7], [4, 4], [3, 7]],

            [[3, 7], [2, 7], [2, 7]],  // truncated start
            [[3, 7], [3, 7], [3, 7]],
            [[3, 7], [4, 7], [3, 7]],
            [[3, 7], [5, 7], [3, 7]],
            [[3, 7], [6, 7], [3, 7]],
            [[3, 7], [7, 7],   null],  // abuts after

            [[3, 7], [2, 8], [2, 8]],  // truncated start and end
            [[3, 7], [3, 8], [3, 8]],  // truncated end
            [[3, 7], [4, 8], [3, 8]],  // truncated end
            [[3, 7], [5, 8], [3, 8]],  // truncated end
            [[3, 7], [6, 8], [3, 8]],  // truncated end
            [[3, 7], [7, 8],   null],  // abuts after
            [[3, 7], [8, 8],   null]   // gap after
        ];
    }

    /**
     * @dataProvider providerJoin
     *
     * @param array      $first    The 1st interval's start and end pair.
     * @param array      $second   The 1st interval's start and end pair.
     * @param array|null $expected The expected interval's start and end pair.
     */
    public function testJoin(array $first, array $second, $expected)
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        if ($expected !== null) {
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->join($secondInterval));
            $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->join($firstInterval));
        } else {
            $this->assertNull($firstInterval->join($secondInterval));
            $this->assertNull($secondInterval->join($firstInterval));
        }
    }

    /**
     * @return array
     */
    public function providerJoin()
    {
        return [
            [[3, 7], [1, 2],   null],  // gap before
            [[3, 7], [2, 2],   null],  // gap before

            [[3, 7], [2, 3], [2, 7]],  // abuts before
            [[3, 7], [3, 3], [3, 7]],  // abuts before

            [[3, 7], [2, 4],   null],  // truncated start
            [[3, 7], [3, 4],   null],
            [[3, 7], [4, 4],   null],

            [[3, 7], [2, 7],   null],  // truncated start
            [[3, 7], [3, 7],   null],
            [[3, 7], [4, 7],   null],
            [[3, 7], [5, 7],   null],
            [[3, 7], [6, 7],   null],
            [[3, 7], [7, 7], [3, 7]],  // abuts after

            [[3, 7], [2, 8],   null],  // truncated start and end
            [[3, 7], [3, 8],   null],  // truncated end
            [[3, 7], [4, 8],   null],  // truncated end
            [[3, 7], [5, 8],   null],  // truncated end
            [[3, 7], [6, 8],   null],  // truncated end
            [[3, 7], [7, 8], [3, 8]],  // abuts after
            [[3, 7], [8, 8],   null]   // gap after
        ];
    }

    /**
     * @dataProvider providerAbuts
     *
     * @param integer $h1             The 1st interval's start hour.
     * @param integer $m1             The 1st interval's start minute.
     * @param integer $h2             The 1st interval's end hour.
     * @param integer $m2             The 1st interval's end minute.
     * @param integer $h3             The 2nd interval's start hour.
     * @param integer $m3             The 2nd interval's start minute.
     * @param integer $h4             The 2nd interval's end hour.
     * @param integer $m4             The 2nd interval's end minute.
     * @param integer $expectedResult The expected result.
     */
    public function testAbuts($h1, $m1, $h2, $m2, $h3, $m3, $h4, $m4, $expectedResult)
    {
        $timeSpan1 = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $timeSpan2 = new Interval($this->createDateTime($h3 * 3600 + $m3 * 60), $this->createDateTime($h4 * 3600 + $m4 * 60));

        $this->assertSame($expectedResult, $timeSpan1->abuts($timeSpan2));
    }

    /**
     * @return array
     */
    public function providerAbuts()
    {
        return [
            // [09:00 to 10:00) abuts [08:00 to 08:30) = false (completely before)
            [
                9, 0, 10,  0,
                8, 0,  8, 30,
                false
            ],

            // [09:00 to 10:00) abuts [08:00 to 09:00) = true
            [
                9, 0, 10, 0,
                8, 0,  9, 0,
                true
            ],

            // [09:00 to 10:00) abuts [08:00 to 09:01) = false (overlaps)
            [
                9, 0, 10, 0,
                8, 0,  9, 1,
                false
            ],

            // [09:00 to 10:00) abuts [09:00 to 09:00) = true
            [
                9, 0, 10, 0,
                9, 0,  9, 0,
                true
            ],

            // [09:00 to 10:00) abuts [09:00 to 09:01) = false (overlaps)
            [
                9, 0, 10, 0,
                9, 0,  9, 1,
                false
            ],

            // [09:00 to 10:00) abuts [10:00 to 10:00) = true
            [
                 9, 0, 10, 0,
                10, 0, 10, 0,
                true
            ],

            // [09:00 to 10:00) abuts [10:00 to 10:30) = true
            [
                 9, 0, 10,  0,
                10, 0, 10, 30,
                true
            ],

            // [09:00 to 10:00) abuts [10:30 to 11:00) = false (completely after)
            [
                 9,  0, 10, 0,
                10, 30, 11, 0,
                false
            ],

            // [14:00 to 14:00) abuts [14:00 to 14:00) = true
            [
                14, 0, 14, 0,
                14, 0, 14, 0,
                true
            ],

            // [14:00 to 14:00) abuts [14:00 to 15:00) = true
            [
                14, 0, 14, 0,
                14, 0, 15, 0,
                true
            ],

            // [14:00 to 14:00) abuts [13:00 to 14:00) = true
            [
                14, 0, 14, 0,
                13, 0, 14, 0,
                true
            ]
        ];
    }

    /**
     * @dataProvider providerContains
     *
     * @param integer $h1             The 1st interval's start hour.
     * @param integer $m1             The 1st interval's start minute.
     * @param integer $h2             The 1st interval's end hour.
     * @param integer $m2             The 1st interval's end minute.
     * @param integer $h3             The 2nd interval's start hour.
     * @param integer $m3             The 2nd interval's start minute.
     * @param integer $h4             The 2nd interval's end hour.
     * @param integer $m4             The 2nd interval's end minute.
     * @param integer $expectedResult The expected result.
     */
    public function testContains($h1, $m1, $h2, $m2, $h3, $m3, $h4, $m4, $expectedResult)
    {
        $timeSpan1 = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $timeSpan2 = new Interval($this->createDateTime($h3 * 3600 + $m3 * 60), $this->createDateTime($h4 * 3600 + $m4 * 60));

        $this->assertSame($expectedResult, $timeSpan1->contains($timeSpan2));
    }

    /**
     * @return array
     */
    public function providerContains()
    {
        return [
            // [09:00 to 10:00) contains [09:00 to 10:00) = true
            [
                9, 0, 10, 0,
                9, 0, 10, 0,
                true
            ],

            // [09:00 to 10:00) contains [09:00 to 09:30) = true
            [
                9, 0, 10,  0,
                9, 0,  9, 30,
                true
            ],

            // [09:00 to 10:00) contains [09:30 to 10:00) = true
            [
                9, 0,  10, 0,
                9, 30, 10, 0,
                true
            ],

            // [09:00 to 10:00) contains [09:15 to 09:45) = true
            [
                9, 0,  10,  0,
                9, 15,  9, 45,
                true
            ],

            // [09:00 to 10:00) contains [09:00 to 09:00) = true
            [
                9, 0, 10, 0,
                9, 0,  9, 0,
                true
            ],

            // [09:00 to 10:00) contains [08:59 to 10:00) = false (otherStart before thisStart)
            [
                9, 0,  10, 0,
                8, 59, 10, 0,
                false
            ],

            // [09:00 to 10:00) contains [09:00 to 10:01) = false (otherEnd after thisEnd)
            [
                9, 0, 10, 0,
                9, 0, 10, 1,
                false
            ],

            // [09:00 to 10:00) contains [10:00 to 10:00) = false (otherStart equals thisEnd)
            [
                 9,  0, 10, 0,
                10,  0, 10, 0,
                false
            ],

            // [14:00 to 14:00) contains [14:00 to 14:00) = false (zero duration contains nothing)
            [
                14,  0, 14, 0,
                14,  0, 14, 0,
                false
            ]
        ];
    }

    /**
     * @dataProvider providerContainsInstant
     *
     * @param integer $h1             The interval's start hour.
     * @param integer $m1             The interval's start minute.
     * @param integer $h2             The interval's end hour.
     * @param integer $m2             The interval's end minute.
     * @param integer $h3             The hour of the test datetime.
     * @param integer $m3             The minute of the test datetime.
     * @param integer $expectedResult The expected result.
     */
    public function testContainsInstant($h1, $m1, $h2, $m2, $h3, $m3, $expectedResult)
    {
        $timeSpan = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $instant = $this->createDateTime($h3 * 3600 + $m3 * 60);

        $this->assertSame($expectedResult, $timeSpan->containsInstant($instant));
    }

    /**
     * @return array
     */
    public function providerContainsInstant()
    {
        return [
            // [09:00 to 10:00) contains 08:59 = false (before start)
            [9, 0, 10, 0, 8, 59, false],

            // [09:00 to 10:00) contains 09:00 = true
            [9, 0, 10, 0, 9, 0, true],

            // [09:00 to 10:00) contains 09:59 = true
            [9, 0, 10, 0, 9, 59, true],

            // [09:00 to 10:00) contains 10:00 = false (equals end)
            [9, 0, 10, 0, 10, 0, false],

            // [09:00 to 10:00) contains 10:01 = false (after end)
            [9, 0, 10, 0, 10, 1, false],

            // [14:00 to 14:00) contains 14:00 = false (zero duration contains nothing)
            [14, 0, 14, 0, 14, 0, false]
        ];
    }

    /**
     * @dataProvider providerOverlaps
     *
     * @param integer $h1             The 1st interval's start hour.
     * @param integer $m1             The 1st interval's start minute.
     * @param integer $h2             The 1st interval's end hour.
     * @param integer $m2             The 1st interval's end minute.
     * @param integer $h3             The 2nd interval's start hour.
     * @param integer $m3             The 2nd interval's start minute.
     * @param integer $h4             The 2nd interval's end hour.
     * @param integer $m4             The 2nd interval's end minute.
     * @param integer $expectedResult The expected result.
     */
    public function testOverlaps($h1, $m1, $h2, $m2, $h3, $m3, $h4, $m4, $expectedResult)
    {
        $timeSpan1 = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $timeSpan2 = new Interval($this->createDateTime($h3 * 3600 + $m3 * 60), $this->createDateTime($h4 * 3600 + $m4 * 60));

        $this->assertSame($expectedResult, $timeSpan1->overlaps($timeSpan2));
    }

    /**
     * @return array
     */
    public function providerOverlaps()
    {
        return [
            // [09:00 to 10:00) overlaps [08:00 to 08:30) = false (completely before)
            [
                9, 0, 10,  0,
                8, 0,  8, 30,
                false
            ],

            // [09:00 to 10:00) contains [08:00 to 09:00) = false (abuts before)
            [
                9, 0, 10, 0,
                8, 0,  9, 0,
                false
            ],

            // [09:00 to 10:00) overlaps [08:00 to 09:30) = true
            [
                9, 0, 10,  0,
                8, 0,  9, 30,
                true
            ],

            // [09:00 to 10:00) overlaps [08:00 to 10:00) = true
            [
                9, 0, 10, 0,
                8, 0, 10, 0,
                true
            ],

            // [09:00 to 10:00) overlaps [08:00 to 11:00) = true
            [
                9, 0, 10, 0,
                8, 0, 11, 0,
                true
            ],

            // [09:00 to 10:00) overlaps [09:00 to 09:00) = false (abuts before)
            [
                9, 0, 10, 0,
                9, 0,  9, 0,
                false
            ],

            // [09:00 to 10:00) overlaps [09:00 to 09:30) = true
            [
                9, 0, 10,  0,
                9, 0,  9, 30,
                true
            ],

            // [09:00 to 10:00) overlaps [09:00 to 10:00) = true
            [
                9, 0, 10, 0,
                9, 0, 10, 0,
                true
            ],

            // [09:00 to 10:00) overlaps [09:00 to 11:00) = true
            [
                9, 0, 10, 0,
                9, 0, 11, 0,
                true
            ],

            // [09:00 to 10:00) overlaps [09:30 to 09:30) = true
            [
                9,  0, 10,  0,
                9, 30,  9, 30,
                true
            ],

            // [09:00 to 10:00) overlaps [09:30 to 10:00) = true
            [
                9,  0, 10, 0,
                9, 30, 10, 0,
                true
            ],

            // [09:00 to 10:00) overlaps [09:30 to 11:00) = true
            [
                9,  0, 10, 0,
                9, 30, 11, 0,
                true
            ],

            // [09:00 to 10:00) overlaps [10:00 to 10:00) = false (abuts after)
            [
                 9, 0, 10, 0,
                10, 0, 10, 0,
                false
            ],

            // [09:00 to 10:00) overlaps [10:00 to 11:00) = false (abuts after)
            [
                 9, 0, 10, 0,
                10, 0, 11, 0,
                false
            ],

            // [09:00 to 10:00) overlaps [10:30 to 11:00) = false (completely after)
            [
                 9,  0, 10, 0,
                10, 30, 11, 0,
                false
            ],

            // [14:00 to 14:00) overlaps [14:00 to 14:00) = false (abuts before and after)
            [
                14, 0, 14, 0,
                14, 0, 14, 0,
                false
            ],

            // [14:00 to 14:00) overlaps [13:00 to 15:00) = true
            [
                14, 0, 14, 0,
                13, 0, 15, 0,
                true
            ]
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param integer $s1             The 1st interval's start second.
     * @param integer $n1             The 1st interval's start micro second.
     * @param integer $s2             The 1st interval's end second.
     * @param integer $n2             The 1st interval's end micro second.
     * @param integer $s3             The 2st interval's start second.
     * @param integer $n3             The 2st interval's start micro second.
     * @param integer $s4             The 2nd interval's end second.
     * @param integer $n4             The 2nd interval's end micro second.
     * @param integer $expectedResult The expected result.
     */
    public function testIsEqualTo($s1, $n1, $s2, $n2, $s3, $n3, $s4, $n4, $expectedResult)
    {
        $timeSpan1 = new Interval($this->createDateTime($s1, $n1), $this->createDateTime($s2, $n2));
        $timeSpan2 = new Interval($this->createDateTime($s3, $n3), $this->createDateTime($s4, $n4));

        $this->assertSame($expectedResult, $timeSpan1->isEqualTo($timeSpan2));
    }

    /**
     * @return array
     */
    public function providerIsEqualTo()
    {
        return [
            [0, 0, 0, 0, 0, 0, 0, 0, true],
            [0, 0, 1, 0, 0, 0, 1, 0, true],
            [0, 0, 1, 0, 0, 0, 1, 1, false],
            [0, 0, 1, 0, 1, 0, 1, 0, false],
            [1, 0, 1, 0, 0, 0, 1, 0, false],
            [1, 1, 1, 1, 1, 1, 1, 1, true],
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param integer $s1             The interval's start second.
     * @param integer $n1             The interval's start micro second.
     * @param integer $s2             The interval's end second.
     * @param integer $n2             The interval's end micro second.
     * @param string  $expectedResult The expected result.
     */
    public function testToString($s1, $n1, $s2, $n2, $expectedResult)
    {
        $timeSpan = new Interval($this->createDateTime($s1, $n1), $this->createDateTime($s2, $n2));

        $this->assertSame($expectedResult, (string) $timeSpan);
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [0, 0, 1, 100000, '1970-01-01T00:00:00.000000Z/1970-01-01T00:00:01.100000Z'],
        ];
    }

    /**
     * @return \DateTimeInterface
     */
    private function createDateTime($seconds, $micros = 0)
    {
        return \DateTimeImmutable::createFromFormat('U.u', sprintf('%d.%06d', $seconds, $micros));
    }

    /**
     * @param integer  $s1       The expected epoch second of the start datetime.
     * @param integer  $n1       The expected micro second adjustment of the start datetime.
     * @param integer  $s1       The expected epoch second of the start datetime.
     * @param integer  $n1       The expected micro second adjustment of the start datetime.
     * @param Interval $timeSpan The interval to test.
     */
    private function assertIntervalIs($s1, $n1, $s2, $n2, Interval $timeSpan)
    {
        $this->compare([$s1, $n1, $s2, $n2], [
            (int) $timeSpan->getStart()->format('U'),
            (int) $timeSpan->getStart()->format('u'),
            (int) $timeSpan->getEnd()->format('U'),
            (int) $timeSpan->getEnd()->format('u')
        ]);
    }

    /**
     * @param array $expected The expected values.
     * @param array $actual   The actual values, count & keys matching expected values.
     */
    private function compare(array $expected, array $actual)
    {
        $message = $this->export($actual) . ' !== ' . $this->export($expected);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $actual[$key], $message);
        }
    }

    /**
     * Exports the given values as a string.
     *
     * @param array $values The values to export.
     *
     * @return string
     */
    private function export(array $values)
    {
        foreach ($values as & $value) {
            $value = var_export($value, true);
        }

        return '(' . implode(', ', $values) . ')';
    }
}
