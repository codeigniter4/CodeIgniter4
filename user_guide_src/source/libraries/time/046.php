<?php

use CodeIgniter\I18n\Time;

$time1 = Time::parse('2024-01-01 12:00:00', 'UTC');
$time2 = Time::parse('2024-01-01 13:00:00', 'UTC');

$time1->min($time2);                // 2024-01-01 12:00:00 UTC
$time2->min('2024-01-01 12:30:00'); // 2024-01-01 12:30:00 UTC
$time2->min('2024-01-01 13:00:00', 'Europe/Warsaw'); // 2024-01-01 12:00:00 UTC
$time1->min();                      // earlier of $time1 and now
