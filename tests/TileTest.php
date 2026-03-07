<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use Tito10047\HgtReader\DataSource\MemoryDataSource;
use Tito10047\HgtReader\DataSource\FileDataSource;
use Tito10047\HgtReader\Resolution;
use Tito10047\HgtReader\Tile;

class TileTest extends TestCase
{
    public function testMemoryDataSource(): void
    {
        // 1201 * 1201 * 2 bytes = 2884802
        $size = 1201 * 1201 * 2;
        $binary = str_repeat("\x00\x05", $size / 2); // Všetky výšky 5m
        
        $source = new MemoryDataSource($binary);
        $tile = new Tile($source, Resolution::Arc3);
        
        $this->assertEquals(5, $tile->getElevationAt(0, 0));
        $this->assertEquals(5, $tile->getElevationAt(600, 600));
        $this->assertEquals(5, $tile->getElevationAt(1200, 1200));
    }

    public function testNegativeElevation(): void
    {
        $size = 1201 * 1201 * 2;
        // -5 v 2's complement big-endian signed short je 0xFFFB
        $binary = str_repeat("\xFF\xFB", $size / 2);
        
        $source = new MemoryDataSource($binary);
        $tile = new Tile($source, Resolution::Arc3);
        
        $this->assertEquals(-5, $tile->getElevationAt(0, 0));
    }

    public function testFileDataSource(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'hgt');
        $size = 1201 * 1201 * 2;
        $binary = str_repeat("\x00\x0A", $size / 2); // 10m
        file_put_contents($tempFile, $binary);
        
        $source = new FileDataSource($tempFile);
        $tile = new Tile($source, Resolution::Arc3);
        
        $this->assertEquals(10, $tile->getElevationAt(0, 0));
        
        $tile->close();
        unlink($tempFile);
    }
}
