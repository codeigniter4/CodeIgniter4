<?php

$image->withFile('/path/to/image/mypic.jpg')
    // processing methods
    ->save('/path/to/image/my_low_quality_pic.jpg', 10);
