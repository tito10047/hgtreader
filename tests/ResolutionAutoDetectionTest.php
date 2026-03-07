<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use Tito10047\HgtReader\HgtReader;
use Tito10047\HgtReader\TileProvider\LocalFileSystemTileProvider;

class ResolutionAutoDetectionTest extends TestCase
{
    public function testAutoDetectArc3(): void
    {
        $provider = new LocalFileSystemTileProvider(__DIR__ . '/assets');
        // No resolution passed to constructor
        $reader = new HgtReader($provider);
        
        $lat = 49.386287689;
        $lon = 19.3770275116;
        
        $elevation = $reader->getElevation($lat, $lon);
        
        // Should detect Arc3 from N49E019.hgt size and calculate correctly
        $this->assertEqualsWithDelta(658.66, $elevation, 0.01);
    }
}
