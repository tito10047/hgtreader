# world elevations reader


## EXAMLE
```php
$lat = 49.386287689;
$lon = 19.3770275116;
HgtReader::init(__DIR__."/m34",3);
$el = HgtReader::getElevation($lat,$lon);
echo "elevation on {$lat},{$lon} is {$el}m";
```

you can download htg files [from here](http://www.viewfinderpanoramas.org/Coverage%20map%20viewfinderpanoramas_org3.htm)



![usage](https://raw.githubusercontent.com/tito10047/hgt-reader/master/example.png)
