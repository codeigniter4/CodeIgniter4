<?php

service('image', 'imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->flip('horizontal')
    ->save('/path/to/new/image.jpg');
