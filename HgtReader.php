<?php


class HgtReader {

	private static $htgFilesDestination;
	private static $resolution  = -1;
	private static $measPerDeg;
	private static $openedFiles = [];

	public static function init($htgFilesDestination, $resolution) {
		self::$htgFilesDestination = $htgFilesDestination;
		self::$resolution          = $resolution;
		switch ($resolution) {
			case 1:
				self::$measPerDeg = 3601;
				break;
			case 3:
				self::$measPerDeg = 1201;
				break;
			default:
				throw new \Exception("bad resolution can be only one of 1,3");
		}
		register_shutdown_function(function () {
			HgtReader::closeAllFiles();
		});
	}

	public static function closeAllFiles() {
		foreach (self::$openedFiles as $file) {
			fclose($file);
		}
		self::$openedFiles = [];
	}

	private static function getElevationAtPosition($fileName, $row, $column) {
		if (!array_key_exists($fileName, self::$openedFiles)) {
			if (!file_exists(self::$htgFilesDestination . DIRECTORY_SEPARATOR . $fileName)) {
				throw new \Exception("File '{$fileName}' not exists.");
			}
			$file = fopen(self::$htgFilesDestination . DIRECTORY_SEPARATOR . $fileName, "r");
			if ($file === false) {
				throw new \Exception("Cant open file '{$fileName}' for reading.");
			}
			self::$openedFiles[$fileName] = $file;
		} else {
			$file = self::$openedFiles[$fileName];
		}

		if ($row > self::$measPerDeg || $column > self::$measPerDeg) {
			//TODO:open next file
			throw new \Exception("Mot implemented yet");
		}
		$aRow     = self::$measPerDeg - $row;
		$position = (self::$measPerDeg * ($aRow - 1)) + $column;
		$position *= 2;
		fseek($file, $position);
		$short  = fread($file, 2);
		$_      = unpack("n*", $short);
		$shorts = reset($_);
		return $shorts;
	}

	/**
	 * @param float $lat
	 * @param float $lon
	 * @param null  $fName
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function getElevation($lat, $lon, &$fName = null) {
		if (self::$resolution == -1) {
			throw new \Exception("use HgtReader::init(ASSETS_HGT . DIRECTORY_SEPARATOR, 3);");
		}
		$N      = self::getDeg($lat, 2);
		$E      = self::getDeg($lon, 3);
		$fName  = "N{$N}E{$E}.hgt";
		$latSec = self::getSec($lat);
		$lonSec = self::getSec($lon);

		$Xn = round($latSec / self::$resolution, 3);
		$Yn = round($lonSec / self::$resolution, 3);

		$a1 = round($Xn);
		$a2 = round($Yn);

		if ($Xn <= $a1 && $Yn <= $a2) {
			$b1 = $a1 - 1;
			$b2 = $a2;
			$c1 = $a1;
			$c2 = $a2 - 1;
		} else if ($Xn >= $a1 && $Yn >= $a2) {
			$b1 = $a1 + 1;
			$b2 = $a2;
			$c1 = $a1;
			$c2 = $a2 + 1;
		} else if ($Xn > $a1 && $Yn < $a2) {
			$b1 = $a1;
			$b2 = $a2 - 1;
			$c1 = $a1 + 1;
			$c2 = $a2;
		} else if ($Xn < $a1 && $Yn > $a2) {
			$b1 = $a1 - 1;
			$b2 = $a2;
			$c1 = $a1;
			$c2 = $a2 + 1;
		} else {
			throw new \Exception("{$Xn}:{$Yn}");
		}


		$a3 = self::getElevationAtPosition($fName, $a1, $a2);
		$b3 = self::getElevationAtPosition($fName, $b1, $b2);
		$c3 = self::getElevationAtPosition($fName, $c1, $c2);

		$n1 = ($c2 - $a2) * ($b3 - $a3) - ($c3 - $a3) * ($b2 - $a2);
		$n2 = ($c3 - $a3) * ($b1 - $a1) - ($c1 - $a1) * ($b3 - $a3);
		$n3 = ($c1 - $a1) * ($b2 - $a2) - ($c2 - $a2) * ($b1 - $a1);

		$d  = -$n1 * $a1 - $n2 * $a2 - $n3 * $a3;
		$zN = (-$n1 * $Xn - $n2 * $Yn - $d) / $n3;

//		echo "{$a1}:{$a2}:{$a3}<br>";
//		echo "{$b1}:{$b2}:{$b3}<br>";
//		echo "{$c1}:{$c2}:{$c3}<br>";
//		echo "{$Xn}:{$Yn}:{$zN}<br>";
//		exit;

		return $zN;
	}

	private static function getDeg($deg, $numPrefix) {
		$deg = abs($deg);
		$d   = round($deg, 0);     // round degrees
		if ($numPrefix >= 3) {
			if ($d < 100) {
				$d = '0' . $d;
			}
		} // pad with leading zeros
		if ($d < 10) {
			$d = '0' . $d;
		}
		return $d;
	}

	private static function getSec($deg) {
		$deg = abs($deg);
		$sec = round($deg * 3600, 4);
		$m   = fmod(floor($sec / 60), 60);
		$s   = round(fmod($sec, 60), 4);
		return ($m * 60) + $s;
	}
}
