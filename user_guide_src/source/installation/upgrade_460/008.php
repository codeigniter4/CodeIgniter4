<?php

use CodeIgniter\I18n\Time;

// The Application Timezone is "UTC".

// Set $time1 timezone to "America/Chicago".
$time1 = Time::parse('2024-08-20', 'America/Chicago');

// The timestamp is "2024-08-20 00:00" in "UTC".
$stamp = strtotime('2024-08-20'); // 1724112000

// But $time2 timezone is "America/Chicago".
$time2 = $time1->setTimestamp($stamp);

echo $time2->format('Y-m-d H:i:s P');
// Before: 2024-08-20 00:00:00 -05:00
//  After: 2024-08-19 19:00:00 -05:00
