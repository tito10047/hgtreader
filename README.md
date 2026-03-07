# world elevations reader


## EXAMPLES

### PHP (Modern 2026 API)
```php
use Mostka\HgtReader\HgtReader;
use Mostka\HgtReader\Resolution;
use Mostka\HgtReader\TileProvider\LocalFileSystemTileProvider;

$lat = 49.386287689;
$lon = 19.3770275116;
$hgtPath = "path/to/hgt";

$provider = new LocalFileSystemTileProvider($hgtPath);
$reader = new HgtReader($provider, Resolution::Arc3);

$el = $reader->getElevation($lat, $lon);
echo "elevation on {$lat},{$lon} is {$el}m";
```

you can download htg files [from here](http://www.viewfinderpanoramas.org/Coverage%20map%20viewfinderpanoramas_org3.htm)

### Purpose

![usage1](example2.png)

![usage2](example.png)
