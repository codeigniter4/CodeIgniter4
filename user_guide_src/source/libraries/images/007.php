<?php

try {
    $image = \Config\Services::image()
        ->withFile('/path/to/image/mypic.jpg')
        ->fit(100, 100, 'center')
        ->save('/path/to/image/mypic_thumb.jpg');
} catch (CodeIgniter\Images\ImageException $e) {
    echo $e->getMessage();
}
