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
    public function testConstructorThrowsInvalidArgumentException(int $s1, int $n1, int $s2, int $n2): void
    {
        new Interval(new \DateTimeImmutable('1970-01-01 00:00:01'), new \DateTimeImmutable('1970-01-01 00:00:00'));
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
    public function testWithStart(int $s, int $n): void
    {
        $interval = new Interval($this->createDateTime(0), $this->createDateTime(100));
        $this->assertIntervalIs($s, $n, 100, 0, $interval->withStart($this->createDateTime($s, $n)));
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
    public function testWithEnd(int $s, int $n): void
    {
        $interval = new Interval($this->createDateTime(0), $this->createDateTime(100));
        $this->assertIntervalIs(0, 0, $s, $n, $interval->withEnd($this->createDateTime($s, $n)));
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
    public function testGetDuration(int $s1, int $m1, int $s2, int $m2, int $s, int $n): void
    {
        $interval = new Interval($this->createDateTime($s1, $m1), $this->createDateTime($s2, $m2));

        $this->assertDurationIs($s, $n, $interval->getDuration());
    }

    public function providerGetDuration(): array
    {
        return [
            [0, 0, 0, 0, 0, 0],
            [1999999999, 555555000, 2000000001, 111000, 1, 444556000],
        ];
    }

    /**
     * @dataProvider providerGap
     */
    public function testGap(array $first, array $second, array $expected): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->gap($secondInterval));
        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->gap($firstInterval));
    }

    public function providerGap(): array
    {
        return [
            [[3, 7], [0, 1], [1, 3]],
            [[3, 7], [1, 1], [1, 3]],
            [[3, 7], [8, 8], [7, 8]],
            [[3, 7], [8, 9], [7, 8]],
            [[3, 7], [9, 9], [7, 9]]
        ];
    }

    /**
     * @dataProvider providerGapReturnsNull
     */
    public function testGapReturnsNull(array $first, array $second): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertNull($firstInterval->gap($secondInterval));
        $this->assertNull($secondInterval->gap($firstInterval));
    }

    public function providerGapReturnsNull(): array
    {
        return [
            [[3, 7], [2, 3]],  // abuts before
            [[3, 7], [3, 3]],  // abuts before
            [[3, 7], [4, 6]],  // overlaps
            [[3, 7], [3, 7]],  // overlaps
            [[3, 7], [6, 7]],  // overlaps
            [[3, 7], [7, 7]],  // abuts before
            [[3, 7], [6, 8]],  // overlaps
            [[3, 7], [7, 8]],  // abuts after
            [[3, 7], [6, 9]],  // overlaps
            [[3, 7], [7, 9]],  // abuts after
        ];
    }

    /**
     * @dataProvider providerOverlap
     */
    public function testOverlap(array $first, array $second, array $expected): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->overlap($secondInterval));
        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->overlap($firstInterval));
    }

    public function providerOverlap(): array
    {
        return [
            [[3, 7], [2, 4], [3, 4]],  // truncated start
            [[3, 7], [3, 4], [3, 4]],
            [[3, 7], [4, 4], [4, 4]],

            [[3, 7], [2, 7], [3, 7]],  // truncated start
            [[3, 7], [3, 7], [3, 7]],
            [[3, 7], [4, 7], [4, 7]],
            [[3, 7], [5, 7], [5, 7]],
            [[3, 7], [6, 7], [6, 7]],

            [[3, 7], [2, 8], [3, 7]],  // truncated start and end
            [[3, 7], [3, 8], [3, 7]],  // truncated end
            [[3, 7], [4, 8], [4, 7]],  // truncated end
            [[3, 7], [5, 8], [5, 7]],  // truncated end
            [[3, 7], [6, 8], [6, 7]],  // truncated end
        ];
    }

    /**
     * @dataProvider providerOverlapReturnsNull
     */
    public function testOverlapReturnsNull(array $first, array $second): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertNull($firstInterval->overlap($secondInterval));
        $this->assertNull($secondInterval->overlap($firstInterval));
    }

    public function providerOverlapReturnsNull(): array
    {
        return [
            [[3, 7], [1, 2]],  // gap before
            [[3, 7], [2, 2]],  // gap before
            [[3, 7], [2, 3]],  // abuts before
            [[3, 7], [3, 3]],  // abuts before
            [[3, 7], [7, 7]],  // abuts after
            [[3, 7], [7, 8]],  // abuts after
            [[3, 7], [8, 8]],  // gap after
        ];
    }

    /**
     * @dataProvider providerCover
     */
    public function testCover(array $first, array $second, array $expected): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->cover($secondInterval));
        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->cover($firstInterval));
    }

    public function providerCover(): array
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
     */
    public function testUnion(array $first, array $second, array $expected): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->union($secondInterval));
        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->union($firstInterval));
    }

    public function providerUnion(): array
    {
        return [
            [[3, 7], [2, 4], [2, 7]],  // truncated start
            [[3, 7], [3, 4], [3, 7]],
            [[3, 7], [4, 4], [3, 7]],

            [[3, 7], [2, 7], [2, 7]],  // truncated start
            [[3, 7], [3, 7], [3, 7]],
            [[3, 7], [4, 7], [3, 7]],
            [[3, 7], [5, 7], [3, 7]],
            [[3, 7], [6, 7], [3, 7]],

            [[3, 7], [2, 8], [2, 8]],  // truncated start and end
            [[3, 7], [3, 8], [3, 8]],  // truncated end
            [[3, 7], [4, 8], [3, 8]],  // truncated end
            [[3, 7], [5, 8], [3, 8]],  // truncated end
            [[3, 7], [6, 8], [3, 8]],  // truncated end
        ];
    }

    /**
     * @dataProvider providerUnionReturnsNull
     */
    public function testUnionReturnsNull(array $first, array $second): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertNull($firstInterval->union($secondInterval));
        $this->assertNull($secondInterval->union($firstInterval));
    }

    public function providerUnionReturnsNull(): array
    {
        return [
            [[3, 7], [1, 2]],  // gap before
            [[3, 7], [2, 2]],  // gap before
            [[3, 7], [2, 3]],  // abuts before
            [[3, 7], [3, 3]],  // abuts before
            [[3, 7], [7, 7]],  // abuts after
            [[3, 7], [7, 8]],  // abuts after
            [[3, 7], [8, 8]]   // gap after
        ];
    }

    /**
     * @dataProvider providerJoin
     */
    public function testJoin(array $first, array $second, array $expected): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $firstInterval->join($secondInterval));
        $this->assertIntervalIs($expected[0], 0, $expected[1], 0, $secondInterval->join($firstInterval));
    }

    public function providerJoin(): array
    {
        return [
            [[3, 7], [2, 3], [2, 7]],  // abuts before
            [[3, 7], [3, 3], [3, 7]],  // abuts before
            [[3, 7], [7, 7], [3, 7]],  // abuts after
            [[3, 7], [7, 8], [3, 8]],  // abuts after
        ];
    }

    /**
     * @dataProvider providerJoinReturnsNull
     */
    public function testJoinReturnsNull(array $first, array $second): void
    {
        $firstInterval = new Interval($this->createDateTime($first[0]), $this->createDateTime($first[1]));
        $secondInterval = new Interval($this->createDateTime($second[0]), $this->createDateTime($second[1]));

        $this->assertNull($firstInterval->join($secondInterval));
        $this->assertNull($secondInterval->join($firstInterval));
    }

    public function providerJoinReturnsNull(): array
    {
        return [
            [[3, 7], [1, 2]],  // gap before
            [[3, 7], [2, 2]],  // gap before

            [[3, 7], [2, 4]],  // truncated start
            [[3, 7], [3, 4]],
            [[3, 7], [4, 4]],

            [[3, 7], [2, 7]],  // truncated start
            [[3, 7], [3, 7]],
            [[3, 7], [4, 7]],
            [[3, 7], [5, 7]],
            [[3, 7], [6, 7]],

            [[3, 7], [2, 8]],  // truncated start and end
            [[3, 7], [3, 8]],  // truncated end
            [[3, 7], [4, 8]],  // truncated end
            [[3, 7], [5, 8]],  // truncated end
            [[3, 7], [6, 8]],  // truncated end
            [[3, 7], [8, 8]]   // gap after
        ];
    }

    /**
     * @dataProvider providerAbuts
     */
    public function testAbuts(int $h1, int $m1, int $h2, int $m2, int $h3, int $m3, int $h4, int $m4, bool $expectedResult): void
    {
        $timeSpan1 = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $timeSpan2 = new Interval($this->createDateTime($h3 * 3600 + $m3 * 60), $this->createDateTime($h4 * 3600 + $m4 * 60));

        $this->assertSame($expectedResult, $timeSpan1->abuts($timeSpan2));
    }

    public function providerAbuts(): array
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
     */
    public function testContains(int $h1, int $m1, int $h2, int $m2, int $h3, int $m3, int $h4, int $m4, bool $expectedResult): void
    {
        $timeSpan1 = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $timeSpan2 = new Interval($this->createDateTime($h3 * 3600 + $m3 * 60), $this->createDateTime($h4 * 3600 + $m4 * 60));

        $this->assertSame($expectedResult, $timeSpan1->contains($timeSpan2));
    }

    public function providerContains(): array
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
     */
    public function testContainsInstant(int $h1, int $m1, int $h2, int $m2, int $h3, int $m3, bool $expectedResult): void
    {
        $interval = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $instant = $this->createDateTime($h3 * 3600 + $m3 * 60);

        $this->assertSame($expectedResult, $interval->containsInstant($instant));
    }

    public function providerContainsInstant(): array
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
     */
    public function testOverlaps(int $h1, int $m1, int $h2, int $m2, int $h3, int $m3, int $h4, int $m4, bool $expectedResult): void
    {
        $timeSpan1 = new Interval($this->createDateTime($h1 * 3600 + $m1 * 60), $this->createDateTime($h2 * 3600 + $m2 * 60));
        $timeSpan2 = new Interval($this->createDateTime($h3 * 3600 + $m3 * 60), $this->createDateTime($h4 * 3600 + $m4 * 60));

        $this->assertSame($expectedResult, $timeSpan1->overlaps($timeSpan2));
    }

    public function providerOverlaps(): array
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
     */
    public function testIsEqualTo(int $s1, int $n1, int $s2, int $n2, int $s3, int $n3, int $s4, int $n4, bool $expectedResult): void
    {
        $timeSpan1 = new Interval($this->createDateTime($s1, $n1), $this->createDateTime($s2, $n2));
        $timeSpan2 = new Interval($this->createDateTime($s3, $n3), $this->createDateTime($s4, $n4));

        $this->assertSame($expectedResult, $timeSpan1->isEqualTo($timeSpan2));
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
    public function testToString(int $s1, int $n1, int $s2, int $n2, string $expectedResult): void
    {
        $interval = new Interval($this->createDateTime($s1, $n1), $this->createDateTime($s2, $n2));

        $this->assertSame($expectedResult, (string) $interval);
    }

    public function providerToString(): array
    {
        return [
            [0, 0, 1, 0, '1970-01-01T00:00:00+00:00/1970-01-01T00:00:01+00:00'],
        ];
    }
}
