<?php

service('image', 'imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->clearMetadata()
    ->save('/path/to/new/image.jpg');
