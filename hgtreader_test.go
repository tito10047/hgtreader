package hgtreader

import (
	"testing"
)

func TestGetElevation(t *testing.T) {

	var hgtPath = "./"
	hr, err := New(hgtPath,3)
	if err!=nil{
		t.Error(err)
	}
	defer hr.Close()
	var lat = 49.386287689
	var lon = 19.3770275116

	el, err := hr.GetElevation(lat,lon)
	if err!=nil{
		t.Error(err)
	}
	if el!=658.66 {
		t.Error("bad returned elevation ",el)
	}
}
