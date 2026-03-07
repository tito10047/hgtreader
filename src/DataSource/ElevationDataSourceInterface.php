<?php

namespace Tito10047\HgtReader\DataSource;

interface ElevationDataSourceInterface
{
    /**
     * Prečíta 2 bajty na danej pozícii a vráti výšku.
     * @param int $offset
     * @return int
     */
    public function readElevationAt(int $offset): int;

    /**
     * Uzatvorí zdroj.
     */
    public function close(): void;

    /**
     * @return int Veľkosť zdroja v bajtoch.
     */
    public function getSize(): int;
}
