<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\TestCase;
use Tito10047\HgtReader\Coordinate;

class CoordinateTest extends TestCase
{
    public function testGetTileName(): void
    {
        $c = new Coordinate(49.386, 19.377);
        $this->assertEquals('N49E019.hgt', $c->getTileName());

        $c = new Coordinate(-49.386, -19.377);
        $this->assertEquals('S50W020.hgt', $c->getTileName());
    }

    public function testGetTileNameNegative(): void
    {
        // -0.5 latitude is in tile S01
        $c = new Coordinate(-0.5, -0.5);
        $this->assertEquals('S01W001.hgt', $c->getTileName());
        
        $c = new Coordinate(48.5, -85.5);
        $this->assertEquals('N48W086.hgt', $c->getTileName());
    }
}
