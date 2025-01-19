<?php

use CodeIgniter\I18n\Time;

$time = Time::now();
echo $time->format('Y-m-d H:i:s.u');
// Before: 2024-07-26 21:39:32.249072
//  After: 2024-07-26 21:39:32.249072
