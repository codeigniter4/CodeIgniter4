<?php

service('image', 'imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->clearMetadata([
        'exif:GPSLatitude',
        'exif:GPSLongitude',
        'exif:GPSAltitude',
    ])
    ->save('/path/to/new/image.jpg');
