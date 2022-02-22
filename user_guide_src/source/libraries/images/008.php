<?php

$info = \Config\Services::image('imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->getFile()
    ->getProperties(true);

$xOffset = ($info['width'] / 2) - 25;
$yOffset = ($info['height'] / 2) - 25;

\Config\Services::image('imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->crop(50, 50, $xOffset, $yOffset)
    ->save('/path/to/new/image.jpg');
