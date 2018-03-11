<?php

declare(strict_types=1);

namespace Emonkak\DateTime;

interface FieldInterface
{
    public function getBaseUnit(): UnitInterface;

    public function getRangeUnit(): UnitInterface;

    public function getMinValue(): int;

    public function getMaxValue(): int;

    public function getFrom(\DateTimeInterface $dateTime): int;

    public function adjustInto(\DateTimeInterface $dateTime, int $newValue): DateTime;

    public function validate(int $value): bool;

    public function __toString(): string;
}
