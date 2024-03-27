<?php

use CodeIgniter\I18n\Time;

// 31 Mar 2024 - Daylight Saving Time Starts
$current = Time::parse('2024-03-31', 'Europe/Madrid');
$test    = Time::parse('2024-04-01', 'Europe/Madrid');

$diff = $current->difference($test);

echo $diff->getDays();
// 0 in v4.4.6 or before
// 1 in v4.4.7 or later
