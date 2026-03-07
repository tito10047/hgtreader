<?php

namespace Tito10047\HgtReader;

readonly class Coordinate
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw new \InvalidArgumentException("Invalid latitude: {$this->latitude}");
        }
        if ($this->longitude < -180 || $this->longitude > 180) {
            throw new \InvalidArgumentException("Invalid longitude: {$this->longitude}");
        }
    }

    public function getTileName(): string
    {
        $lat = (int)floor($this->latitude);
        $lon = (int)floor($this->longitude);

        $latPrefix = $lat >= 0 ? 'N' : 'S';
        $lonPrefix = $lon >= 0 ? 'E' : 'W';

        return sprintf('%s%02d%s%03d.hgt', $latPrefix, abs($lat), $lonPrefix, abs($lon));
    }
}
