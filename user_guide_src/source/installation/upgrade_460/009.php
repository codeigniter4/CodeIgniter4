<?php

use CodeIgniter\I18n\Time;

// The Application Timezone is "America/Chicago".

// $time1 timezone is "America/Chicago".
$time1 = Time::parse('2024-08-20');

// The timestamp is "2024-08-20 00:00" in "America/Chicago".
$stamp = strtotime('2024-08-20'); // 1724130000

// $time2 timezone is also "America/Chicago".
$time2 = $time1->setTimestamp($stamp);

echo $time2->format('Y-m-d H:i:s P');
// Before: 2024-08-20 00:00:00 -05:00
//  After: 2024-08-20 00:00:00 -05:00
