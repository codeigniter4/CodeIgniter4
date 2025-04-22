<?php

service('image', 'imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->clearMetadata([
        'except' => ['exif:Copyright', 'exif:Author'],
    ])
    ->save('/path/to/new/image.jpg');
