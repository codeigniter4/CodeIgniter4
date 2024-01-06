<?php

\Config\Services::image('imagick')
    ->withFile('/path/to/image/mypic.png')
    ->flatten()
    ->save('/path/to/new/image.jpg');

\Config\Services::image('imagick')
    ->withFile('/path/to/image/mypic.png')
    ->flatten(25, 25, 112)
    ->save('/path/to/new/image.jpg');
