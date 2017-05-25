<?php
/**
 * Created by PhpStorm.
 * User: Jozef Môstka
 * Date: 25.5.2017
 * Time: 8:36
 */

require_once __DIR__.'/HgtReader.php';

$lat = 49.386287689;
$lon = 19.3770275116;
$hgtPath = "./";
HgtReader::init($hgtPath,3);
$el = HgtReader::getElevation($lat,$lon);
if ($el.""==658.66){
	echo "PASS".PHP_EOL;
}else{
	echo "FAILED ".PHP_EOL;
}
