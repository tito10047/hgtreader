<?php

use Tito10047\HgtReader\Coordinate;
use Tito10047\HgtReader\HgtReader as NewHgtReader;
use Tito10047\HgtReader\Resolution;
use Tito10047\HgtReader\TileProvider\LocalFileSystemTileProvider;

/**
 * @deprecated Use \Tito10047\HgtReader\HgtReader instead. This class is a legacy wrapper.
 */
class HgtReader {

	private static ?NewHgtReader $instance = null;
	private static ?string       $destination = null;
	private static ?int          $res = null;

	/**
	 * Records the configuration. The reader itself is built lazily on the first getElevation()
	 * call, so init() never touches the filesystem — callers that init() on every request must
	 * not fail just because the tile directory is missing.
	 *
	 * @param string   $htgFilesDestination directory holding the .hgt tiles
	 * @param int|null $resolution          1 (SRTM-1) or 3 (SRTM-3); null = detect from file size
	 */
	public static function init($htgFilesDestination, $resolution = null) {
		if (self::$destination !== $htgFilesDestination || self::$res !== $resolution) {
			self::$instance    = null; // __destruct closes the open tiles
			self::$destination = $htgFilesDestination;
			self::$res         = $resolution;
		}
	}

	/**
	 * Closes every open .hgt file. The configuration from init() is kept, so the next
	 * getElevation() transparently reopens what it needs.
	 */
	public static function closeAllFiles() {
		self::$instance = null; // Destructor in the new HgtReader will close all sources (DataSource::close)
	}

	public static function getElevation($lat, $lon, &$fName = null) {
		if (func_num_args() > 2) {
			$coord = new Coordinate($lat, $lon);
			$fName = $coord->getTileName();
		}

		return self::reader()->getElevation($lat, $lon);
	}

	private static function reader(): NewHgtReader {
		if (self::$instance === null) {
			if (self::$destination === null) {
				throw new \Exception("Use HgtReader::init(..., ...);");
			}
			$resEnum = self::$res === null
				? null
				: (self::$res === 1 ? Resolution::Arc1 : Resolution::Arc3);

			self::$instance = new NewHgtReader(
				new LocalFileSystemTileProvider(self::$destination),
				$resEnum
			);
		}

		return self::$instance;
	}
}
