<?php

namespace Tito10047\HgtReader\TileProvider;

use Tito10047\HgtReader\Coordinate;
use Tito10047\HgtReader\DataSource\ElevationDataSourceInterface;

interface TileProviderInterface
{
    /**
     * @param Coordinate $coordinate
     * @return ElevationDataSourceInterface Data source for the .hgt file
     * @throws \Exception If the file does not exist or cannot be read
     */
    public function getTileSource(Coordinate $coordinate): ElevationDataSourceInterface;
}
