<?php

namespace Tito10047\HgtReader;

use Tito10047\HgtReader\DataSource\ElevationDataSourceInterface;

class Tile
{
    private readonly Resolution $resolution;

    public function __construct(
        private readonly ElevationDataSourceInterface $dataSource,
        ?Resolution $resolution = null
    ) {
        $this->resolution = $resolution ?? Resolution::fromFileSize($this->dataSource->getSize());
        
        $expectedSize = $this->resolution->getMeasurementsPerDegree() ** 2 * 2;
        if ($this->dataSource->getSize() !== $expectedSize) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid data source size. Expected %d bytes, got %d.",
                $expectedSize,
                $this->dataSource->getSize()
            ));
        }
    }

    public function getElevationAt(int $row, int $column): int
    {
        $gridSize = $this->resolution->getMeasurementsPerDegree();
        
        if ($row < 0 || $row >= $gridSize || $column < 0 || $column >= $gridSize) {
            throw new \OutOfBoundsException("Coordinates [{$row}, {$column}] out of tile grid.");
        }

        $aRow = $gridSize - $row;
        $offset = (($gridSize * ($aRow - 1)) + $column) * 2;
        
        return $this->dataSource->readElevationAt($offset);
    }

    public function getResolution(): Resolution
    {
        return $this->resolution;
    }

    public function close(): void
    {
        $this->dataSource->close();
    }
}
