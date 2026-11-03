<?php

use CodeIgniter\I18n\Time;

$time1 = new Time('2024-01-01 12:00:00.654321');
$time2 = new Time('2024-01-01 12:00:00');

// Removes the microseconds.
$time1 = Time::createFromFormat(
    'Y-m-d H:i:s',
    $time1->format('Y-m-d H:i:s'),
    $time1->getTimezone(),
);

$time1->equals($time2);
// Before: true
//  After: true
