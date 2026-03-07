<?php

namespace Tito10047\HgtReader\TileProvider;

use Tito10047\HgtReader\Coordinate;
use Tito10047\HgtReader\DataSource\ElevationDataSourceInterface;

interface TileProviderInterface
{
    /**
     * @param Coordinate $coordinate
     * @return ElevationDataSourceInterface Zdroj dát pre .hgt súbor
     * @throws \Exception Ak súbor neexistuje alebo sa nedá prečítať
     */
    public function getTileSource(Coordinate $coordinate): ElevationDataSourceInterface;
}
