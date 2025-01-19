<?php

use CodeIgniter\Files\FileSizeUnit;

$bytes     = $file->getSizeByBinaryUnit(); // 256901
$kibibytes = $file->getSizeByBinaryUnit(FileSizeUnit::KB); // 250.880
$mebibytes = $file->getSizeByBinaryUnit(FileSizeUnit::MB); // 0.245
