<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\Interval;

/**
 * @covers Emonkak\DateTime\Interval
 */
class IntervalTest extends AbstractTestCase
{
    /**
     * @dataProvider providerConstructorThrowsInvalidArgumentException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testConstructorThrowsInvalidArgumentException(int $second1, int $micro1, int $second2, int $micro2): void
    {
        $this->createInterval($second1, $micro1, $second2, $micro2);
    }

    public function providerConstructorThrowsInvalidArgumentException(): array
    {
        return [
            [1, 0, 0, 0],
            [0, 1, 0, 0],
        ];
    }

    /**
     * @dataProvider providerWithStart
     */
    public function testWithStart(int $seconds, int $micros): void
    {
        $interval = $this->createInterval(0, 0, 100, 0);
        $this->assertIntervalIs($seconds, $micros, 100, 0, $interval->withStart($this->createDateTime($seconds, $micros)));
    }

    public function providerWithStart(): array
    {
        return [
            [12, 34000],
            [0, 34000],
            [12, 0],
            [0, 0]
        ];
    }

    /**
     * @dataProvider providerWithEnd
     */
    public function testWithEnd(int $seconds, int $micros): void
    {
        $interval = $this->createInterval(0, 0, 100, 0);
        $this->assertIntervalIs(0, 0, $seconds, $micros, $interval->withEnd($this->createDateTime($seconds, $micros)));
    }

    public function providerWithEnd(): array
    {
        return [
            [12, 34000],
            [0, 34000],
            [12, 0],
            [0, 0]
        ];
    }

    /**
     * @dataProvider providerGetDuration
     */
    public function testGetDuration(int $second1, int $micro1, int $second2, int $micro2, int $expectedSeconds, int $expectedMicros): void
    {
        $interval = $this->createInterval($second1, $micro1, $second2, $micro2);

        $this->assertDurationIs($expectedSeconds, $expectedMicros, $interval->getDuration());
    }

    public function providerGetDuration(): array
    {
        return [
            [0, 0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1, 0],
            [1999999, 555555, 2000001, 111, 1, 444556],
        ];
    }

    /**
     * @dataProvider providerGap
     */
    public function testGap(int $s1, int $s2, int $s3, int $s4, int $es1, int $es2): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertIntervalIs($es1, 0, $es2, 0, $interval1->gap($interval2));
        $this->assertIntervalIs($es1, 0, $es2, 0, $interval2->gap($interval1));
    }

    public function providerGap(): array
    {
        return [
            [3, 7, 0, 1, 1, 3],
            [3, 7, 1, 1, 1, 3],
            [3, 7, 8, 8, 7, 8],
            [3, 7, 8, 9, 7, 8],
            [3, 7, 9, 9, 7, 9]
        ];
    }

    /**
     * @dataProvider providerGapReturnsNull
     */
    public function testGapReturnsNull(int $s1, int $s2, int $s3, int $s4): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertNull($interval1->gap($interval2));
        $this->assertNull($interval2->gap($interval1));
    }

    public function providerGapReturnsNull(): array
    {
        return [
            [3, 7, 2, 3],  // abuts before
            [3, 7, 3, 3],  // abuts before
            [3, 7, 4, 6],  // overlaps
            [3, 7, 3, 7],  // overlaps
            [3, 7, 6, 7],  // overlaps
            [3, 7, 7, 7],  // abuts before
            [3, 7, 6, 8],  // overlaps
            [3, 7, 7, 8],  // abuts after
            [3, 7, 6, 9],  // overlaps
            [3, 7, 7, 9],  // abuts after
        ];
    }

    /**
     * @dataProvider providerOverlap
     */
    public function testOverlap(int $s1, int $s2, int $s3, int $s4, int $es1, int $es2): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertIntervalIs($es1, 0, $es2, 0, $interval1->overlap($interval2));
        $this->assertIntervalIs($es1, 0, $es2, 0, $interval2->overlap($interval1));
    }

    public function providerOverlap(): array
    {
        return [
            [3, 7, 2, 4, 3, 4],  // truncated start
            [3, 7, 3, 4, 3, 4],
            [3, 7, 4, 4, 4, 4],

            [3, 7, 2, 7, 3, 7],  // truncated start
            [3, 7, 3, 7, 3, 7],
            [3, 7, 4, 7, 4, 7],
            [3, 7, 5, 7, 5, 7],
            [3, 7, 6, 7, 6, 7],

            [3, 7, 2, 8, 3, 7],  // truncated start and end
            [3, 7, 3, 8, 3, 7],  // truncated end
            [3, 7, 4, 8, 4, 7],  // truncated end
            [3, 7, 5, 8, 5, 7],  // truncated end
            [3, 7, 6, 8, 6, 7],  // truncated end
        ];
    }

    /**
     * @dataProvider providerOverlapReturnsNull
     */
    public function testOverlapReturnsNull(int $s1, int $s2, int $s3, int $s4): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertNull($interval1->overlap($interval2));
        $this->assertNull($interval2->overlap($interval1));
    }

    public function providerOverlapReturnsNull(): array
    {
        return [
            [3, 7, 1, 2],  // gap before
            [3, 7, 2, 2],  // gap before
            [3, 7, 2, 3],  // abuts before
            [3, 7, 3, 3],  // abuts before
            [3, 7, 7, 7],  // abuts after
            [3, 7, 7, 8],  // abuts after
            [3, 7, 8, 8],  // gap after
        ];
    }

    /**
     * @dataProvider providerCover
     */
    public function testCover(int $s1, int $s2, int $s3, int $s4, int $es1, int $es2): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertIntervalIs($es1, 0, $es2, 0, $interval1->cover($interval2));
        $this->assertIntervalIs($es1, 0, $es2, 0, $interval2->cover($interval1));
    }

    public function providerCover(): array
    {
        return [
            [3, 7, 1, 2, 1, 7],  // gap before
            [3, 7, 2, 2, 2, 7],  // gap before

            [3, 7, 2, 3, 2, 7],  // abuts before
            [3, 7, 3, 3, 3, 7],  // abuts before

            [3, 7, 2, 4, 2, 7],  // truncated start
            [3, 7, 3, 4, 3, 7],
            [3, 7, 4, 4, 3, 7],

            [3, 7, 2, 7, 2, 7],  // truncated start
            [3, 7, 3, 7, 3, 7],
            [3, 7, 4, 7, 3, 7],
            [3, 7, 5, 7, 3, 7],
            [3, 7, 6, 7, 3, 7],
            [3, 7, 7, 7, 3, 7],  // abuts after

            [3, 7, 2, 8, 2, 8],  // truncated start and end
            [3, 7, 3, 8, 3, 8],  // truncated end
            [3, 7, 4, 8, 3, 8],  // truncated end
            [3, 7, 5, 8, 3, 8],  // truncated end
            [3, 7, 6, 8, 3, 8],  // truncated end
            [3, 7, 7, 8, 3, 8],  // abuts after
            [3, 7, 8, 8, 3, 8]   // gap after
        ];
    }

    /**
     * @dataProvider providerUnion
     */
    public function testUnion(int $s1, int $s2, int $s3, int $s4, int $es1, int $es2): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertIntervalIs($es1, 0, $es2, 0, $interval1->union($interval2));
        $this->assertIntervalIs($es1, 0, $es2, 0, $interval2->union($interval1));
    }

    public function providerUnion(): array
    {
        return [
            [3, 7, 2, 4, 2, 7],  // truncated start
            [3, 7, 3, 4, 3, 7],
            [3, 7, 4, 4, 3, 7],

            [3, 7, 2, 7, 2, 7],  // truncated start
            [3, 7, 3, 7, 3, 7],
            [3, 7, 4, 7, 3, 7],
            [3, 7, 5, 7, 3, 7],
            [3, 7, 6, 7, 3, 7],

            [3, 7, 2, 8, 2, 8],  // truncated start and end
            [3, 7, 3, 8, 3, 8],  // truncated end
            [3, 7, 4, 8, 3, 8],  // truncated end
            [3, 7, 5, 8, 3, 8],  // truncated end
            [3, 7, 6, 8, 3, 8],  // truncated end
        ];
    }

    /**
     * @dataProvider providerUnionReturnsNull
     */
    public function testUnionReturnsNull(int $s1, int $s2, int $s3, int $s4): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertNull($interval1->union($interval2));
        $this->assertNull($interval2->union($interval1));
    }

    public function providerUnionReturnsNull(): array
    {
        return [
            [3, 7, 1, 2],  // gap before
            [3, 7, 2, 2],  // gap before
            [3, 7, 2, 3],  // abuts before
            [3, 7, 3, 3],  // abuts before
            [3, 7, 7, 7],  // abuts after
            [3, 7, 7, 8],  // abuts after
            [3, 7, 8, 8]   // gap after
        ];
    }

    /**
     * @dataProvider providerJoin
     */
    public function testJoin(int $s1, int $s2, int $s3, int $s4, int $es1, int $es2): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertIntervalIs($es1, 0, $es2, 0, $interval1->join($interval2));
        $this->assertIntervalIs($es1, 0, $es2, 0, $interval2->join($interval1));
    }

    public function providerJoin(): array
    {
        return [
            [3, 7, 2, 3, 2, 7],  // abuts before
            [3, 7, 3, 3, 3, 7],  // abuts before
            [3, 7, 7, 7, 3, 7],  // abuts after
            [3, 7, 7, 8, 3, 8],  // abuts after
        ];
    }

    /**
     * @dataProvider providerJoinReturnsNull
     */
    public function testJoinReturnsNull(int $s1, int $s2, int $s3, int $s4): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertNull($interval1->join($interval2));
        $this->assertNull($interval2->join($interval1));
    }

    public function providerJoinReturnsNull(): array
    {
        return [
            [3, 7, 1, 2],  // gap before
            [3, 7, 2, 2],  // gap before

            [3, 7, 2, 4],  // truncated start
            [3, 7, 3, 4],
            [3, 7, 4, 4],

            [3, 7, 2, 7],  // truncated start
            [3, 7, 3, 7],
            [3, 7, 4, 7],
            [3, 7, 5, 7],
            [3, 7, 6, 7],

            [3, 7, 2, 8],  // truncated start and end
            [3, 7, 3, 8],  // truncated end
            [3, 7, 4, 8],  // truncated end
            [3, 7, 5, 8],  // truncated end
            [3, 7, 6, 8],  // truncated end
            [3, 7, 8, 8]   // gap after
        ];
    }

    /**
     * @dataProvider providerAbuts
     */
    public function testAbuts(int $s1, int $s2, int $s3, int $s4, bool $expectedResult): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertSame($expectedResult, $interval1->abuts($interval2));
    }

    public function providerAbuts(): array
    {
        return [
            // [09:00 to 10:00) abuts [08:00 to 08:30) = false (completely before)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                8 * 3600 + 30 * 60,
                false
            ],

            // [09:00 to 10:00) abuts [08:00 to 09:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) abuts [08:00 to 09:01) = false (overlaps)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                9 * 3600 + 1 * 60,
                false
            ],

            // [09:00 to 10:00) abuts [09:00 to 09:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) abuts [09:00 to 09:01) = false (overlaps)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                9 * 3600 + 1 * 60,
                false
            ],

            // [09:00 to 10:00) abuts [10:00 to 10:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) abuts [10:00 to 10:30) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 30 * 60,
                true
            ],

            // [09:00 to 10:00) abuts [10:30 to 11:00) = false (completely after)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 30 * 60,
                11 * 3600 + 0 * 60,
                false
            ],

            // [14:00 to 14:00) abuts [14:00 to 14:00) = true
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                true
            ],

            // [14:00 to 14:00) abuts [14:00 to 15:00) = true
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                15 * 3600 + 0 * 60,
                true
            ],

            // [14:00 to 14:00) abuts [13:00 to 14:00) = true
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                13 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                true
            ]
        ];
    }

    /**
     * @dataProvider providerContains
     */
    public function testContains(int $s1, int $s2, int $s3, int $s4, bool $expectedResult): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertSame($expectedResult, $interval1->contains($interval2));
    }

    public function providerContains(): array
    {
        return [
            // [09:00 to 10:00) contains [09:00 to 10:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) contains [09:00 to 09:30) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                true
            ],

            // [09:00 to 10:00) contains [09:30 to 10:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                10 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) contains [09:15 to 09:45) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 15 * 60,
                9 * 3600 + 45 * 60,
                true
            ],

            // [09:00 to 10:00) contains [09:00 to 09:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) contains [08:59 to 10:00) = false (otherStart before thisStart)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 59 * 60,
                10 * 3600 + 0 * 60,
                false
            ],

            // [09:00 to 10:00) contains [09:00 to 10:01) = false (otherEnd after thisEnd)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                10 * 3600 + 1 * 60,
                false
            ],

            // [09:00 to 10:00) contains [10:00 to 10:00) = false (otherStart equals thisEnd)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                false
            ],

            // [14:00 to 14:00) contains [14:00 to 14:00) = false (zero duration contains nothing)
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                false
            ]
        ];
    }

    /**
     * @dataProvider providerContainsDateTime
     */
    public function testContainsDateTime(int $s1, int $s2, int $s3, bool $expectedResult): void
    {
        $interval = $this->createInterval($s1, 0, $s2, 0);
        $dateTime = $this->createDateTime($s3, 0);

        $this->assertSame($expectedResult, $interval->containsDateTime($dateTime));
    }

    public function providerContainsDateTime(): array
    {
        return [
            // [09:00 to 10:00) contains 08:59 = false (before start)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 59 * 60,
                false
            ],

            // [09:00 to 10:00) contains 09:00 = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) contains 09:59 = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 59 * 60,
                true
            ],

            // [09:00 to 10:00) contains 10:00 = false (equals end)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                false
            ],

            // [09:00 to 10:00) contains 10:01 = false (after end)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 1 * 60,
                false
            ],

            // [14:00 to 14:00) contains 14:00 = false (zero duration contains nothing)
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                false
            ]
        ];
    }

    /**
     * @dataProvider providerOverlaps
     */
    public function testOverlaps(int $s1, int $s2, int $s3, int $s4, bool $expectedResult): void
    {
        $interval1 = $this->createInterval($s1, 0, $s2, 0);
        $interval2 = $this->createInterval($s3, 0, $s4, 0);

        $this->assertSame($expectedResult, $interval1->overlaps($interval2));
    }

    public function providerOverlaps(): array
    {
        return [
            // [09:00 to 10:00) overlaps [08:00 to 08:30) = false (completely before)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                8 * 3600 + 30 * 60,
                false
            ],

            // [09:00 to 10:00) contains [08:00 to 09:00) = false (abuts before)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                false
            ],

            // [09:00 to 10:00) overlaps [08:00 to 09:30) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [08:00 to 10:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [08:00 to 11:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                8 * 3600 + 0 * 60,
                11 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [09:00 to 09:00) = false (abuts before)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                false
            ],

            // [09:00 to 10:00) overlaps [09:00 to 09:30) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [09:00 to 10:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [09:00 to 11:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 0 * 60,
                11 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [09:30 to 09:30) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                9 * 3600 + 30 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [09:30 to 10:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                10 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [09:30 to 11:00) = true
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                9 * 3600 + 30 * 60,
                11 * 3600 + 0 * 60,
                true
            ],

            // [09:00 to 10:00) overlaps [10:00 to 10:00) = false (abuts after)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                false
            ],

            // [09:00 to 10:00) overlaps [10:00 to 11:00) = false (abuts after)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                11 * 3600 + 0 * 60,
                false
            ],

            // [09:00 to 10:00) overlaps [10:30 to 11:00) = false (completely after)
            [
                9 * 3600 + 0 * 60,
                10 * 3600 + 0 * 60,
                10 * 3600 + 30 * 60,
                11 * 3600 + 0 * 60,
                false
            ],

            // [14:00 to 14:00) overlaps [14:00 to 14:00) = false (abuts before and after)
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                false
            ],

            // [14:00 to 14:00) overlaps [13:00 to 15:00) = true
            [
                14 * 3600 + 0 * 60,
                14 * 3600 + 0 * 60,
                13 * 3600 + 0 * 60,
                15 * 3600 + 0 * 60,
                true
            ]
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     */
    public function testIsEqualTo(int $second1, int $micro1, int $second2, int $micro2, int $second3, int $micro3, int $second4, int $micro4, bool $expectedResult): void
    {
        $interval1 = $this->createInterval($second1, $micro1, $second2, $micro2);
        $interval2 = $this->createInterval($second3, $micro3, $second4, $micro4);

        $this->assertSame($expectedResult, $interval1->isEqualTo($interval2));
    }

    public function providerIsEqualTo(): array
    {
        return [
            [0, 0, 0, 0, 0, 0, 0, 0, true],
            [0, 0, 1, 0, 0, 0, 1, 0, true],
            [0, 0, 1, 0, 0, 0, 1, 1000, false],
            [0, 0, 1, 0, 1, 0, 1, 0, false],
            [1, 0, 1, 0, 0, 0, 1, 0, false],
            [1, 1000, 1, 1000, 1, 1000, 1, 1000, true],
        ];
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString(int $second1, int $micro1, int $second2, int $micro2, string $expectedResult): void
    {
        $interval = $this->createInterval($second1, $micro1, $second2, $micro2);

        $this->assertSame($expectedResult, (string) $interval);
    }

    public function providerToString(): array
    {
        return [
            [0, 0, 1, 0, '1970-01-01T00:00:00+00:00/1970-01-01T00:00:01+00:00'],
        ];
    }

    protected function createInterval(int $second1, int $micro1, int $second2, int $micro2): Interval
    {
        $dateTime1 = $this->createDateTime($second1, $micro1);
        $dateTime2 = $this->createDateTime($second2, $micro2);
        return new Interval($dateTime1, $dateTime2);
    }

    protected function createDateTime(int $second, int $micro = 0): \DateTimeInterface
    {
        $text = sprintf('%d.%06d', $second, $micro);
        return \DateTimeImmutable::createFromFormat('U.u', $text);
    }
}
