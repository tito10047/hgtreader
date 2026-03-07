<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use HgtReader as LegacyHgtReader;

class LegacyHgtReaderTest extends TestCase
{
    protected function setUp(): void
    {
        // Pôvodná classa HgtReader je v roote, je v classmape v composer.json
    }

    public function testLegacyElevation(): void
    {
        // Inicializácia starej classy (resolution 3 = Arc3)
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
        // Vynútenie nulového stavu (keďže je to statická classa)
        $reflection = new \ReflectionClass(LegacyHgtReader::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setValue(null, null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("use HgtReader::init(..., ...);");

        LegacyHgtReader::getElevation(49, 19);
    }
}
