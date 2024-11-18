<?php
use CodeIgniter\Files\FileSizeUnit;

$bytes     = $file->getSizeByUnitBinary(); // 256901
$kibibytes = $file->getSizeByUnitBinary(FileSizeUnit::KB); // 250.880
$mebibytes = $file->getSizeByUnitBinary(FileSizeUnit::MB); // 0.245
