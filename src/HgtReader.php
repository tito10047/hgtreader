<?php

namespace Tito10047\HgtReader;

use Tito10047\HgtReader\TileProvider\TileProviderInterface;

class HgtReader
{
    /** @var array<string, Tile> */
    private array $loadedTiles = [];

    public function __construct(
        private readonly TileProviderInterface $tileProvider,
        private readonly ?Resolution $resolution = null
    ) {
    }

    public function getElevation(float $latitude, float $longitude): float
    {
        $coordinate = new Coordinate($latitude, $longitude);
        $tile = $this->getTile($coordinate);

        $resolution = $tile->getResolution();

        // Original algorithm uses seconds within an hour (0-3600)
        $relLat = $latitude - floor($latitude);
        $relLon = $longitude - floor($longitude);
        
        $latSec = $relLat * 3600;
        $lonSec = $relLon * 3600;

        $Xn = round($latSec / $resolution->value, 3);
        $Yn = round($lonSec / $resolution->value, 3);

        $a1 = (int)round($Xn);
        $a2 = (int)round($Yn);

        if ($Xn <= $a1 && $Yn <= $a2) {
            $b1 = $a1 - 1;
            $b2 = $a2;
            $c1 = $a1;
            $c2 = $a2 - 1;
        } else if ($Xn >= $a1 && $Yn >= $a2) {
            $b1 = $a1 + 1;
            $b2 = $a2;
            $c1 = $a1;
            $c2 = $a2 + 1;
        } else if ($Xn > $a1 && $Yn < $a2) {
            $b1 = $a1;
            $b2 = $a2 - 1;
            $c1 = $a1 + 1;
            $c2 = $a2;
        } else if ($Xn < $a1 && $Yn > $a2) {
            $b1 = $a1 - 1;
            $b2 = $a2;
            $c1 = $a1;
            $c2 = $a2 + 1;
        } else {
            throw new \Exception("{$Xn}:{$Yn}");
        }

        // Near the south / west edge of a tile the neighbouring samples b and c can fall on
        // index -1, which is outside the grid. Mirror such a step to the opposite side of the
        // anchor instead of clamping it: clamping would collapse b (or c) onto a, degenerating
        // the triangle and making $n3 zero.
        $max = $resolution->getMeasurementsPerDegree() - 1;
        $b1 = $this->mirror($b1, $a1, $max);
        $b2 = $this->mirror($b2, $a2, $max);
        $c1 = $this->mirror($c1, $a1, $max);
        $c2 = $this->mirror($c2, $a2, $max);

        // In original code, row is a1 and column is a2
        // In SRTM coordinates: row 0 is the northern edge.
        // Original code in getElevationAtPosition does: $aRow = self::$measPerDeg - $row;
        // So if latSec=0 (south), row=0, aRow=1201 (last row). Correct.
        
        $a3 = $tile->getElevationAt($a1, $a2);
        $b3 = $tile->getElevationAt($b1, $b2);
        $c3 = $tile->getElevationAt($c1, $c2);

        $n1 = ($c2 - $a2) * ($b3 - $a3) - ($c3 - $a3) * ($b2 - $a2);
        $n2 = ($c3 - $a3) * ($b1 - $a1) - ($c1 - $a1) * ($b3 - $a3);
        $n3 = ($c1 - $a1) * ($b2 - $a2) - ($c2 - $a2) * ($b1 - $a1);

        $d  = -$n1 * $a1 - $n2 * $a2 - $n3 * $a3;
        $zN = (-$n1 * $Xn - $n2 * $Yn - $d) / $n3;

        return $zN;
    }

    /**
     * Flips a grid index that left the [0, $max] range to the opposite side of $anchor,
     * keeping it exactly one step away so the interpolation triangle stays non-degenerate.
     */
    private function mirror(int $value, int $anchor, int $max): int
    {
        return ($value < 0 || $value > $max) ? 2 * $anchor - $value : $value;
    }

    private function getTile(Coordinate $coordinate): Tile
    {
        $tileName = $coordinate->getTileName();
        if (!isset($this->loadedTiles[$tileName])) {
            $source = $this->tileProvider->getTileSource($coordinate);
            $this->loadedTiles[$tileName] = new Tile($source, $this->resolution);
        }

        return $this->loadedTiles[$tileName];
    }

    public function __destruct()
    {
        foreach ($this->loadedTiles as $tile) {
            $tile->close();
        }
    }
}
