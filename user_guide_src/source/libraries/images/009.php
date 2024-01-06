<?php

\Config\Services::image()
    ->withFile('/path/to/image/mypic.jpg')
    ->convert(IMAGETYPE_PNG)
    ->save('/path/to/new/image.png');
