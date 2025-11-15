<?php

$image = \Config\Services::image();

$image
    ->withFile('/path/to/image/mypic.jpg')
    ->resize(75, 50, true)
    ->save('/path/to/image/mypic_thumb.jpg');
