<?php

namespace Tito10047\HgtReader\TileProvider;

use Tito10047\HgtReader\Coordinate;
use Tito10047\HgtReader\DataSource\ElevationDataSourceInterface;
use Tito10047\HgtReader\DataSource\FileDataSource;

class LocalFileSystemTileProvider implements TileProviderInterface
{
    public function __construct(private readonly string $basePath)
    {
        if (!is_dir($this->basePath)) {
            throw new \InvalidArgumentException("Directory '{$this->basePath}' does not exist.");
        }
    }

    public function getTileSource(Coordinate $coordinate): ElevationDataSourceInterface
    {
        $fileName = $coordinate->getTileName();
        $filePath = $this->basePath . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($filePath)) {
            throw new \Exception("Tile file '{$fileName}' not found in '{$this->basePath}'.");
        }

        return new FileDataSource($filePath);
    }
}
