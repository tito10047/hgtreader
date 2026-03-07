<?php

namespace Tito10047\HgtReader\DataSource;

class FileDataSource implements ElevationDataSourceInterface
{
    /** @var resource|null */
    private $handle;
    private readonly int $size;

    public function __construct(private readonly string $filePath)
    {
        $this->handle = @fopen($this->filePath, 'rb');
        if ($this->handle === false) {
            throw new \RuntimeException("Failed to open file: {$this->filePath}");
        }
        $this->size = filesize($this->filePath);
    }

    public function readElevationAt(int $offset): int
    {
        if ($this->handle === null) {
            throw new \RuntimeException("DataSource is closed.");
        }

        if ($offset < 0 || $offset + 2 > $this->size) {
            throw new \OutOfBoundsException("Offset {$offset} out of bounds for file data source.");
        }

        if (fseek($this->handle, $offset) !== 0) {
            throw new \RuntimeException("Failed to seek in file.");
        }

        $data = fread($this->handle, 2);
        if ($data === false || strlen($data) !== 2) {
            throw new \RuntimeException("Failed to read from file.");
        }

        $val = unpack('n', $data)[1];

        // Handle signed short
        if ($val >= 32768) {
            $val -= 65536;
        }

        return $val;
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
        $this->handle = null;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function __destruct()
    {
        $this->close();
    }
}
