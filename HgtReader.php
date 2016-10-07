<?php

function sqr($x){
    return $x*$x;
}
class HgtReader{

    private static $htgFilesDestination;
    private static $resolution;
    private static $measPerDeg;
    private static $openedFiles=[];

    public static function init($htgFilesDestination, $resolution){
        self::$htgFilesDestination=$htgFilesDestination;
        self::$resolution=$resolution;
        switch ($resolution){
            case 1:self::$measPerDeg=3601;break;
            case 3:self::$measPerDeg=1201;break;
            default:
                throw new \Exception("bad resolution can be only one of 1,3");
        }
        register_shutdown_function(function (){
            HgtReader::closeAllFiles();
        });
    }

    public static function closeAllFiles(){
        foreach(self::$openedFiles as $file){
            fclose($file);
        }
        self::$openedFiles=[];
    }

    private static function getElevationAtPosition($fileName,$row,$column){
        if (!array_key_exists($fileName,self::$openedFiles)){
            if (!file_exists(self::$htgFilesDestination.DIRECTORY_SEPARATOR.$fileName)){
                throw new \Exception("File '{$fileName}' not exists.");
            }
            $file=fopen(self::$htgFilesDestination.DIRECTORY_SEPARATOR.$fileName,"r");
            if ($file===false){
                throw new \Exception("Cant open file '{$fileName}' for reading.");
            }
            self::$openedFiles[$fileName]=$file;
        }else{
            $file=self::$openedFiles[$fileName];
        }
        $aRow=self::$measPerDeg-$row;
        $position = (self::$measPerDeg * ($aRow-1) ) + $column;
        $position*=2;
        fseek($file,$position);
        $short = fread($file,2);
        $_=unpack("n*", $short);
        $shorts = reset($_);
        return $shorts;
    }

    public static function getElevation($lat, $lon){
        $N=self::getDeg($lat,2);
        $E=self::getDeg($lon,3);
        $fName="N{$N}E{$E}.hgt";
        $latSec = self::getSec($lat);
        $lonSec = self::getSec($lon);

        $row = round($latSec/self::$resolution);
        $column = round($lonSec/self::$resolution);

        $gird_size=1000;
        $x=round($latSec/self::$resolution,2)*100;
        $y=round($lonSec/self::$resolution,2)*100;
        $nnx=round($latSec/self::$resolution)*100;
        $nny=round($lonSec/self::$resolution)*100;

        if ($nnx<$x && $nny<$y){ // lavo hore
            $nx=$x-$nnx+$gird_size;
            $ny=$y-$nny+$gird_size;
            $xa=$gird_size;
            $ya=$gird_size;
            $xb=$gird_size;
            $yb=$gird_size*2;
            $xc=$gird_size*2;
            $yc=$gird_size;
            $za=self::getElevationAtPosition($fName,$row,$column);
            $zb=self::getElevationAtPosition($fName,$row,$column+1);
            $zc=self::getElevationAtPosition($fName,$row+1,$column);
        }elseif($nnx<$x && $nny<$y){ // lavo dole
            $nx=$x-$nnx+$gird_size;
            $ny=$gird_size-($nny-$y);
            $xa=$gird_size;
            $ya=$gird_size;
            $xb=$gird_size*2;
            $yb=$gird_size;
            $xc=$gird_size;
            $yc=0;
            $za=self::getElevationAtPosition($fName,$row,$column);
            $zb=self::getElevationAtPosition($fName,$row+1,$column);
            $zc=self::getElevationAtPosition($fName,$row,$column-1);
        }elseif ($nnx>$x && $nny<$y){ // pravo hore
            echo "3".PHP_EOL;
            $nx=$gird_size-($nnx-$x);
            $ny=$y-$nny+$gird_size;
            $xa=$gird_size;
            $ya=$gird_size;
            $xb=0;
            $yb=$gird_size;
            $xc=$gird_size;
            $yc=$gird_size*2;
            $za=self::getElevationAtPosition($fName,$row,$column);
            $zb=self::getElevationAtPosition($fName,$row-1,$column);
            $zc=self::getElevationAtPosition($fName,$row,$column+1);
        }elseif ($nnx>$x && $nny>$y){ // pravo dole
            $nx=$gird_size-($nnx-$x);
            $ny=$gird_size-($nny-$y);
            $xa=0;
            $ya=$gird_size;
            $xb=$gird_size;
            $yb=0;
            $xc=$gird_size;
            $yc=$gird_size;
            $za=self::getElevationAtPosition($fName,$row,$column);
            $zb=self::getElevationAtPosition($fName,$row,$column-1);
            $zc=self::getElevationAtPosition($fName,$row-1,$column);
        }else{
            if ($x==$nnx && $y==$nny){
                return self::getElevationAtPosition($fName,$row,$column);
            }
            if ($x==$nnx){
                if ($y<$nny){
                    $zie=self::getElevationAtPosition($fName,$row,$column);
                    $ziem=self::getElevationAtPosition($fName,$row,$column-1);
                    if ($zie>$ziem){
                        return ((($zie-$ziem)/$gird_size)*($gird_size-($nny-$y))+$ziem);
                    }elseif ($zie<$ziem){
                        return (((($ziem-$zie)/$gird_size)*($nny-$y))+$zie);
                    }else{
                        return $zie;
                    }
                }else{
                    $zie=self::getElevationAtPosition($fName,$row,$column);
                    $ziep=self::getElevationAtPosition($fName,$row,$column+1);
                    if ($zie>$ziep){
                        return ((($zie-$ziep)/$gird_size)*($gird_size-($y-$nny))+$ziep);
                    }elseif ($zie<$ziep){
                        return (((($ziep-$zie)/$gird_size)*($y-$nny))+$zie);
                    }else{
                        return $zie;
                    }
                }
            }elseif($y==$nny){
                if ($x<$nnx){
                    $zie=self::getElevationAtPosition($fName,$row,$column);
                    $zime=self::getElevationAtPosition($fName,$row-1,$column);
                    if ($zie>$zime){
                        return (((($zie-$zime)/$gird_size)*($gird_size-($nnx-$x)))+$zime);
                    }elseif ($zie<$zime){
                        return (((($zime-$zie)/$gird_size)*($nnx-$x))+$zie);
                    }else{
                        return $zie;
                    }
                }else{
                    $zie=self::getElevationAtPosition($fName,$row,$column);
                    $zipe=self::getElevationAtPosition($fName,$row+1,$column);
                    if ($zie<$zipe){
                        return (((($zie-$zipe)/$gird_size)*($gird_size-($x-$nnx)))+$zipe);
                    }elseif($zie>$zipe){
                        return (((($zipe-$zie)/$gird_size)*($x-$nnx))+$zie);
                    }else{
                        return $zie;
                    }
                }
            }else{
                throw new Exception("something is wrong");
            }
        }

        $xn = (($ya-$yb)*($zc-$zb))-(($za-$zb)*($yc-$yb));
        $yn = (($za-$zb)*($xc-$xb))-(($xa-$xb)*($zc-$zb));
        $zn = (($xa-$xb)*($yc-$yb))-(($ya-$yb)*($xc-$xb));
        $aaa=(sqrt(sqr($xn)+sqr($yn)+sqr($zn)));
        return round((abs($xn*$nx+ $yn*$ny + 0 + (-$xn*$xb - $yn*$yb - $zn*$zb)) / $aaa),2);
    }

    private static function getDeg($deg,$numPrefix){
        $deg = abs($deg);
        $d = round($deg, 0);     // round degrees
        if ($numPrefix>=3)
            if ($d<100) $d = '0' . $d; // pad with leading zeros
        if ($d<10) $d = '0' . $d;
        return $d;
    }

    private static function getSec($deg){
        $deg = abs($deg);
        $sec = round($deg*3600, 4);
        $m = fmod(floor($sec/60), 60);
        $s = round(fmod($sec, 60), 4);
        return ($m*60)+$s;
    }
}
