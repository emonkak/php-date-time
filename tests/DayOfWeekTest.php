<?php

declare(strict_types=1);

namespace Emonkak\DateTime\Tests;

use Emonkak\DateTime\DayOfWeek;

class DayOfWeekTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     * @runInSeparateProcess
     */
    public function testOf(int $value): void
    {
        $this->assertDayOfWeekIs($value, DayOfWeek::of($value));
    }

    public function providerOf(): array
    {
        return [
            [DayOfWeek::MONDAY],
            [DayOfWeek::TUESDAY],
            [DayOfWeek::WEDNESDAY],
            [DayOfWeek::THURSDAY],
            [DayOfWeek::FRIDAY],
            [DayOfWeek::SATURDAY],
            [DayOfWeek::SUNDAY]
        ];
    }

    /**
     * @dataProvider providerOfInvalidDayOfWeekThrowsException
     * @expectedException Emonkak\DateTime\DateTimeException
     */
    public function testOfInvalidDayOfWeekThrowsException(int $dayOfWeek): void
    {
        DayOfWeek::of($dayOfWeek);
    }

    public function providerOfInvalidDayOfWeekThrowsException(): array
    {
        return [
            [-1],
            [0],
            [8]
        ];
    }

    public function testAll(): void
    {
        for ($day = DayOfWeek::MONDAY; $day <= DayOfWeek::SUNDAY; $day++) {
            $firstDayOfWeek = DayOfWeek::of($day);

            foreach (DayOfWeek::all($firstDayOfWeek) as $i => $dayOfWeek) {
                $this->assertTrue($dayOfWeek->isEqualTo($firstDayOfWeek->plus($i)));
            }
        }
    }

    public function testMonday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::MONDAY, DayOfWeek::monday());
    }

    public function testTuesday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::TUESDAY, DayOfWeek::tuesday());
    }

    public function testWednesday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::WEDNESDAY, DayOfWeek::wednesday());
    }

    public function testThursday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::THURSDAY, DayOfWeek::thursday());
    }

    public function testFriday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::FRIDAY, DayOfWeek::friday());
    }

    public function testSaturday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::SATURDAY, DayOfWeek::saturday());
    }

    public function testSunday(): void
    {
        $this->assertDayOfWeekIs(DayOfWeek::SUNDAY, DayOfWeek::sunday());
    }

    public function testIs(): void
    {
        for ($i = DayOfWeek::MONDAY; $i <= DayOfWeek::SUNDAY; $i++) {
            for ($j = DayOfWeek::MONDAY; $j <= DayOfWeek::SUNDAY; $j++) {
                $this->assertSame($i === $j, DayOfWeek::of($i)->is($j));
            }
        }
    }

    public function testIsEqualTo(): void
    {
        for ($i = DayOfWeek::MONDAY; $i <= DayOfWeek::SUNDAY; $i++) {
            for ($j = DayOfWeek::MONDAY; $j <= DayOfWeek::SUNDAY; $j++) {
                $this->assertSame($i === $j, DayOfWeek::of($i)->isEqualTo(DayOfWeek::of($j)));
            }
        }
    }

    /**
     * @dataProvider providerPlus
     */
    public function testPlus(int $dayOfWeek, int $plusDays, int $expectedDayOfWeek): void
    {
        $this->assertDayOfWeekIs($expectedDayOfWeek, DayOfWeek::of($dayOfWeek)->plus($plusDays));
    }

    /**
     * @dataProvider providerPlus
     */
    public function testMinus(int $dayOfWeek, int $plusDays, int $expectedDayOfWeek): void
    {
        $this->assertDayOfWeekIs($expectedDayOfWeek, DayOfWeek::of($dayOfWeek)->minus(-$plusDays));
    }

    public function providerPlus(): \Generator
    {
        for ($dayOfWeek = DayOfWeek::MONDAY; $dayOfWeek <= DayOfWeek::SUNDAY; $dayOfWeek++) {
            for ($plusDays = -15; $plusDays <= 15; $plusDays++) {
                $expectedDayOfWeek = $dayOfWeek + $plusDays;

                while ($expectedDayOfWeek < 1) {
                    $expectedDayOfWeek += 7;
                }
                while ($expectedDayOfWeek > 7) {
                    $expectedDayOfWeek -= 7;
                }

                yield [$dayOfWeek, $plusDays, $expectedDayOfWeek];
            }
        }
    }

    public function providerGetDayOfWeekFromLocalDate(): array
    {
        return [
            ['2000-01-01', DayOfWeek::SATURDAY],
            ['2001-01-01', DayOfWeek::MONDAY],
            ['2002-01-01', DayOfWeek::TUESDAY],
            ['2003-01-01', DayOfWeek::WEDNESDAY],
            ['2004-01-01', DayOfWeek::THURSDAY],
            ['2005-01-01', DayOfWeek::SATURDAY],
            ['2006-01-01', DayOfWeek::SUNDAY],
            ['2007-01-01', DayOfWeek::MONDAY],
            ['2008-01-01', DayOfWeek::TUESDAY],
            ['2009-01-01', DayOfWeek::THURSDAY],
            ['2010-01-01', DayOfWeek::FRIDAY],
            ['2011-01-01', DayOfWeek::SATURDAY],
            ['2012-01-01', DayOfWeek::SUNDAY]
        ];
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString(int $dayOfWeek, string $expectedName): void
    {
        $this->assertSame($expectedName, (string) DayOfWeek::of($dayOfWeek));
    }

    public function providerToString(): array
    {
        return [
            [DayOfWeek::MONDAY,    'Monday'],
            [DayOfWeek::TUESDAY,   'Tuesday'],
            [DayOfWeek::WEDNESDAY, 'Wednesday'],
            [DayOfWeek::THURSDAY,  'Thursday'],
            [DayOfWeek::FRIDAY,    'Friday'],
            [DayOfWeek::SATURDAY,  'Saturday'],
            [DayOfWeek::SUNDAY,    'Sunday']
        ];
    }
}
