<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use Tito10047\HgtReader\Resolution;

class ResolutionTest extends TestCase
{
    public function testGetMeasurementsPerDegree(): void
    {
        $this->assertEquals(3601, Resolution::Arc1->getMeasurementsPerDegree());
        $this->assertEquals(1201, Resolution::Arc3->getMeasurementsPerDegree());
    }

    public function testFromFileSizeArc3(): void
    {
        // 1201 * 1201 * 2 = 2,884,802
        $size = 1201 ** 2 * 2;
        $this->assertEquals(Resolution::Arc3, Resolution::fromFileSize($size));
    }

    public function testFromFileSizeArc1(): void
    {
        // 3601 * 3601 * 2 = 25,934,402
        $size = 3601 ** 2 * 2;
        $this->assertEquals(Resolution::Arc1, Resolution::fromFileSize($size));
    }

    public function testFromFileSizeInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported HGT file size: 123 bytes.");
        Resolution::fromFileSize(123);
    }
}
