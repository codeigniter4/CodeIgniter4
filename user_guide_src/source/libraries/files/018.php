<?php
use CodeIgniter\Files\FileSizeUnit;

$bytes     = $file->getSizeByUnitMetric(); // 256901
$kilobytes = $file->getSizeByUnitMetric(FileSizeUnit::KB); // 256.901
$megabytes = $file->getSizeByUnitMetric(FileSizeUnit::MB); // 0.256
