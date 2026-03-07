<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use Tito10047\HgtReader\HgtReader;
use Tito10047\HgtReader\TileProvider\LocalFileSystemTileProvider;
use Tito10047\HgtReader\Resolution;

class HgtReaderIntegrationTest extends TestCase
{
    public function testOriginalExample(): void
    {
        // Original test.php used lat=49.386287689, lon=19.3770275116 and expected 658.66
        $lat = 49.386287689;
        $lon = 19.3770275116;
        
        $provider = new LocalFileSystemTileProvider(__DIR__ . '/assets');
        $reader = new HgtReader($provider, Resolution::Arc3);
        
        $elevation = $reader->getElevation($lat, $lon);
        
        $this->assertEqualsWithDelta(658.66, $elevation, 0.01);
    }

    public function testM16Data(): void
    {
        // M16/N48W085.hgt moved to tests/assets/N48W085.hgt
        $lat = 48.5;
        $lon = -84.5; 
        
        $provider = new LocalFileSystemTileProvider(__DIR__ . '/assets');
        $reader = new HgtReader($provider, Resolution::Arc3);
        
        $elevation = $reader->getElevation($lat, $lon);
        $this->assertIsFloat($elevation);
    }

    public function testP33Data(): void
    {
        // P33v2/P33/N60E012.hgt moved to tests/assets/N60E012.hgt
        $lat = 60.5;
        $lon = 12.5; 
        
        $provider = new LocalFileSystemTileProvider(__DIR__ . '/assets');
        $reader = new HgtReader($provider, Resolution::Arc3);
        
        $elevation = $reader->getElevation($lat, $lon);
        $this->assertIsFloat($elevation);
    }
}
