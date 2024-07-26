<?php

use CodeIgniter\I18n\Time;

$time = Time::createFromFormat('Y-m-d H:i:s.u', '2024-07-09 09:13:34.654321');
echo $time->format('Y-m-d H:i:s.u');
// Before: 2024-07-09 09:13:34.000000
//  After: 2024-07-09 09:13:34.654321
