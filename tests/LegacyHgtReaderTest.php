<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use HgtReader as LegacyHgtReader;

class LegacyHgtReaderTest extends TestCase
{
    protected function setUp(): void
    {
        // Original HgtReader class is in the root, it is in the classmap in composer.json
    }

    public function testLegacyElevation(): void
    {
        // Initialization of the old class (resolution 3 = Arc3)
        LegacyHgtReader::init(__DIR__ . '/assets', 3);

        $lat = 49.386287689;
        $lon = 19.3770275116;

        $fName = '';
        $elevation = LegacyHgtReader::getElevation($lat, $lon, $fName);

        $this->assertEquals('N49E019.hgt', $fName);
        $this->assertEqualsWithDelta(658.66, $elevation, 0.01);

        LegacyHgtReader::closeAllFiles();
    }

    public function testLegacyM16Data(): void
    {
        LegacyHgtReader::init(__DIR__ . '/assets', 3);
        
        $lat = 48.5;
        $lon = -84.5; 
        
        $elevation = LegacyHgtReader::getElevation($lat, $lon);
        $this->assertIsFloat($elevation);

        LegacyHgtReader::closeAllFiles();
    }

    public function testLegacyP33Data(): void
    {
        LegacyHgtReader::init(__DIR__ . '/assets', 3);
        
        $lat = 60.5;
        $lon = 12.5; 
        
        $elevation = LegacyHgtReader::getElevation($lat, $lon);
        $this->assertIsFloat($elevation);

        LegacyHgtReader::closeAllFiles();
    }

    public function testExceptionIfNotInitialized(): void
    {
        // Force null state (since it's a static class)
        $reflection = new \ReflectionClass(LegacyHgtReader::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Use HgtReader::init(..., ...);");

        LegacyHgtReader::getElevation(49, 19);
    }
}
