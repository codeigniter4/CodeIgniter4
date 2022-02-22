<?php

\Config\Services::image('imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->fit(100, 150, 'left')
    ->save('/path/to/new/image.jpg');
