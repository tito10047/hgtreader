<?php

namespace Tito10047\HgtReader\DataSource;

class MemoryDataSource implements ElevationDataSourceInterface
{
    private readonly int $size;

    public function __construct(private string $content)
    {
        $this->size = strlen($this->content);
    }

    public function readElevationAt(int $offset): int
    {
        if ($offset < 0 || $offset + 2 > $this->size) {
            throw new \OutOfBoundsException("Offset {$offset} out of bounds for memory data source.");
        }

        $data = substr($this->content, $offset, 2);
        $val = unpack('n', $data)[1];

        // Handle signed short
        if ($val >= 32768) {
            $val -= 65536;
        }

        return $val;
    }

    public function close(): void
    {
        $this->content = '';
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
