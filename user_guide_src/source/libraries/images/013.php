<?php

service('image', 'imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->resize(200, 100, true, 'height')
    ->save('/path/to/new/image.jpg');
