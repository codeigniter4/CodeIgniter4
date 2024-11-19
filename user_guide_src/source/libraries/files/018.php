<?php

use CodeIgniter\Files\FileSizeUnit;

$bytes     = $file->getSizeByMetricUnit(); // 256901
$kilobytes = $file->getSizeByMetricUnit(FileSizeUnit::KB); // 256.901
$megabytes = $file->getSizeByMetricUnit(FileSizeUnit::MB); // 0.256
