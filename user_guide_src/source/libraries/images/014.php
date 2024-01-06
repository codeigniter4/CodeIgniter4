<?php

\Config\Services::image('imagick')
    ->withFile('/path/to/image/mypic.jpg')
    ->text('Copyright 2017 My Photo Co', [
        'color'      => '#fff',
        'opacity'    => 0.5,
        'withShadow' => true,
        'hAlign'     => 'center',
        'vAlign'     => 'bottom',
        'fontSize'   => 20,
    ])
    ->save('/path/to/new/image.jpg');
