<?php

namespace Tito10047\HgtReader;

enum Resolution: int
{
    case Arc1 = 1;
    case Arc3 = 3;

    public function getMeasurementsPerDegree(): int
    {
        return match ($this) {
            self::Arc1 => 3601,
            self::Arc3 => 1201,
        };
    }
}
