<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('August 12, 2016 4:15:23pm');

// The output may vary based on locale.
echo $time->getDayOfWeek();   // '6'
echo $time->getDayOfYear();   // '225'
echo $time->getWeekOfMonth(); // '2'
echo $time->getWeekOfYear();  // '33'
echo $time->getTimestamp();   // 1471018523 - UNIX timestamp (locale independent)
echo $time->getQuarter();     // '3'

echo $time->dayOfWeek;   // '6'
echo $time->dayOfYear;   // '225'
echo $time->weekOfMonth; // '2'
echo $time->weekOfYear;  // '33'
echo $time->timestamp;   // 1471018523
echo $time->quarter;     // '3'
