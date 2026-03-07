<?php

namespace Tito10047\HgtReader;

enum Resolution: int {

	case Arc1 = 1;
	case Arc3 = 3;

	public function getMeasurementsPerDegree(): int {
		return match ($this) {
			self::Arc1 => 3601,
			self::Arc3 => 1201,
		};
	}

	public static function fromFileSize(int $bytes): self {
		foreach (self::cases() as $resolution) {
			if ($bytes === $resolution->getMeasurementsPerDegree() ** 2 * 2) {
				return $resolution;
			}
		}
		throw new \InvalidArgumentException(sprintf(
			"Unsupported HGT file size: %d bytes. Expected 2,884,802 (SRTM-3) or 25,934,402 (SRTM-1).",
			$bytes
		));
	}
}
