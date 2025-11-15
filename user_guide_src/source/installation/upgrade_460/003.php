<?php

use CodeIgniter\I18n\Time;

$time = new Time('1 hour ago');
echo $time->format('Y-m-d H:i:s.u');
// Before: 2024-07-26 21:05:57.000000
//  After: 2024-07-26 21:05:57.857235
