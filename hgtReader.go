package hgtreader

import (
	"errors"
	"math"
	"os"
	"strconv"
	"fmt"
	"bufio"
	"encoding/binary"
)

type latLon struct {
	lat float64
	lon float64
}

type reader struct {
	hgtFilesDir string
	resolution  int
	measPerDeg  float64
	hgtFiles    map[string]*os.File
}

func New(hgtFilesDestination string, resolution int) (*reader, error) {
	dir, err := os.Stat(hgtFilesDestination)
	if err != nil {
		return nil, err
	}
	if !dir.IsDir() {
		return nil, errors.New("bad directory")
	}
	var measPerDeg float64
	switch resolution {
	case 1:
		measPerDeg = 3601
		break
	case 3:
		measPerDeg = 1201
		break
	default:
		return nil, errors.New("bad resolution")
	}
	return &reader{
		hgtFilesDestination,
		resolution,
		measPerDeg,
		make(map[string]*os.File),
	}, nil
}

func (hr *reader) Close() {
	for _, value := range hr.hgtFiles {
		value.Close()
	}
}

func (hr *reader) GetElevation(lat, lon float64) (float64, error) {
	var latSec = getSec(lat)
	var lonSec = getSec(lon)

	var Xn = roundTo(latSec/float64(hr.resolution), 3)
	var Yn = roundTo(lonSec/float64(hr.resolution), 3)

	var a1 = round(Xn)
	var a2 = round(Yn)
	var b1, b2, c1, c2 float64
	if Xn <= a1 && Yn <= a2 {
		b1 = a1 - 1
		b2 = a2
		c1 = a1
		c2 = a2 - 1
	} else if Xn >= a1 && Yn >= a2 {
		b1 = a1 + 1
		b2 = a2
		c1 = a1
		c2 = a2 + 1
	} else if Xn > a1 && Yn < a2 {
		b1 = a1
		b2 = a2 - 1
		c1 = a1 + 1
		c2 = a2
	} else if Xn < a1 && Yn > a2 {
		b1 = a1 - 1
		b2 = a2
		c1 = a1
		c2 = a2 + 1
	} else {
		panic(strconv.Itoa(int(Xn)) + ":" + strconv.Itoa(int(Yn)))
	}

	a3, err := hr.getElevationAtPosition(latLon{lat, lon}, a1, a2)
	if err != nil {
		return nil, err
	}
	b3, err := hr.getElevationAtPosition(latLon{lat, lon}, b1, b2)
	if err != nil {
		return nil, err
	}
	c3, err := hr.getElevationAtPosition(latLon{lat, lon}, c1, c2)
	if err != nil {
		return nil, err
	}

	var n1 = (c2-a2)*(b3-a3) - (c3-a3)*(b2-a2)
	var n2 = (c3-a3)*(b1-a1) - (c1-a1)*(b3-a3)
	var n3 = (c1-a1)*(b2-a2) - (c2-a2)*(b1-a1)
	var d = -n1*a1 - n2*a2 - n3*a3
	var zN = (-n1*Xn - n2*Yn - d) / n3
	return zN, nil
}
func (hr *reader) getElevationAtPosition(latlon latLon, row float64, column float64) (float64, error) {
	var addN, addE float64 = 0, 0
	for row > hr.measPerDeg {
		addN++
		row -= hr.measPerDeg
	}
	for column > hr.measPerDeg {
		addE++
		column -= hr.measPerDeg
	}
	var N = prepend(getDeg(latlon.lat)+addN, 2)
	var E = prepend(getDeg(latlon.lon)+addE, 3)
	var fName = fmt.Sprintf("N%sE%s.hgt", N, E)
	var file, ok = hr.hgtFiles[fName]
	if ok == false {
		file, err := os.Open(hr.hgtFilesDir + "/" + fName)
		if err != nil {
			return 0, err
		}
		hr.hgtFiles[fName] = file
	}

	var aRow = hr.measPerDeg - row
	var position = ( hr.measPerDeg * (aRow - 1) ) + column
	position *= 2
	var short int16
	file.Seek(int64(position), 0)
	reader := bufio.NewReader(file)
	binary.Read(reader, binary.LittleEndian, &short)
	return float64(short), nil
}

func prepend(num float64, numPrefix int) string {
	var degI = int(num)
	var ret = ""
	if numPrefix >= 3 {
		if degI < 100 {
			ret += "0"
		}
	}
	if degI < 10 {
		ret += "0"
	}
	return ret + strconv.Itoa(degI)
}
func getDeg(deg float64) float64 {
	return math.Floor(math.Abs(deg))
}
func round(f float64) float64 {
	return math.Floor(f + .5)
}
func roundTo(f float64, places int) float64 {
	shift := math.Pow(10, float64(places))
	return round(f*shift) / shift
}
func getSec(deg float64) float64 {
	deg = math.Abs(deg)
	var sec = deg * 3600
	var m = math.Mod(math.Floor(sec/60), 60)
	var s = math.Mod(sec, 60)
	return m*60 + s
}
