<?php

use Tito10047\HgtReader\Coordinate;
use Tito10047\HgtReader\HgtReader as NewHgtReader;
use Tito10047\HgtReader\Resolution;
use Tito10047\HgtReader\TileProvider\LocalFileSystemTileProvider;

/**
 * @deprecated Use \Mostka\HgtReader\HgtReader instead. This class is a legacy wrapper.
 */
class HgtReader {

	private static ?NewHgtReader $instance = null;
    private static string $destination;
    private static int $res;

	public static function init($htgFilesDestination, $resolution) {
        self::$destination = $htgFilesDestination;
        self::$res = $resolution;
        $provider = new LocalFileSystemTileProvider($htgFilesDestination);
        $resEnum = $resolution === 1 ? Resolution::Arc1 : Resolution::Arc3;
        self::$instance = new NewHgtReader($provider, $resEnum);
	}

	public static function closeAllFiles() {
        self::$instance = null; // Destructor in the new HgtReader will close all sources (DataSource::close)
	}

	public static function getElevation($lat, $lon, &$fName = null) {
		if (self::$instance === null) {
			throw new \Exception("Use HgtReader::init(..., ...);");
		}
        
        if ($fName !== null || func_num_args() > 2) {
            $coord = new Coordinate($lat, $lon);
            $fName = $coord->getTileName();
        }

		return self::$instance->getElevation($lat, $lon);
	}
}
