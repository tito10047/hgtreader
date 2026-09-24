<?php

namespace Tito10047\HgtReader\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tito10047\HgtReader\HgtReader;
use Tito10047\HgtReader\TileProvider\LocalFileSystemTileProvider;

/**
 * Points sitting within half a sample of a whole-degree parallel or meridian used to push the
 * interpolation triangle onto grid index -1, which Tile rejects with an OutOfBoundsException.
 * HgtReader now mirrors such a step to the other side of the anchor.
 */
class TileEdgeTest extends TestCase
{
    private function reader(): HgtReader
    {
        return new HgtReader(new LocalFileSystemTileProvider(__DIR__ . '/assets'));
    }

    public static function edgeCoordinatesProvider(): array
    {
        return [
            'south-west corner'   => [49.0, 19.0],
            'south edge'          => [49.0, 19.5],
            'west edge'           => [49.5, 19.0],
            'just north of south' => [49.0001, 19.0001],
            'quarter sample in'   => [49.0002, 19.0002],
            'north-east corner'   => [49.99999, 19.99999],
            'north edge'          => [49.99999, 19.5],
            'east edge'           => [49.5, 19.99999],
        ];
    }

    #[DataProvider('edgeCoordinatesProvider')]
    public function testEdgeCoordinatesDoNotThrow(float $lat, float $lon): void
    {
        $elevation = $this->reader()->getElevation($lat, $lon);

        $this->assertIsFloat($elevation);
        // N49E019 covers Orava; anything outside this band means the stencil read garbage.
        $this->assertGreaterThan(0, $elevation);
        $this->assertLessThan(3000, $elevation);
    }

    /**
     * Mirroring must not collapse b or c onto a — that would make the triangle degenerate
     * and divide by zero.
     */
    public function testEdgeElevationIsCloseToItsNeighbour(): void
    {
        $reader = $this->reader();

        $onEdge = $reader->getElevation(49.0, 19.0);
        $inside = $reader->getElevation(49.0 + 3 / 3600, 19.0 + 3 / 3600);

        $this->assertEqualsWithDelta($inside, $onEdge, 100.0);
    }
}
