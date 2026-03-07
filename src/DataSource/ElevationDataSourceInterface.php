<?php

namespace Tito10047\HgtReader\DataSource;

interface ElevationDataSourceInterface
{
    /**
     * Reads 2 bytes at the given position and returns the elevation.
     * @param int $offset
     * @return int
     */
    public function readElevationAt(int $offset): int;

    /**
     * Closes the source.
     */
    public function close(): void;

    /**
     * @return int Source size in bytes.
     */
    public function getSize(): int;
}
