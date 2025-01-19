<?php

use CodeIgniter\I18n\Time;

$time1 = new Time('2024-01-01 12:00:00.654321');
$time2 = new Time('2024-01-01 12:00:00');

$time1->equals($time2);
// Before: true
//  After: false
