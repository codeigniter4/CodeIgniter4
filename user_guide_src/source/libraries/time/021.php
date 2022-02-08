<?php

$time = Time::parse('August 12, 2016 4:15:23pm');

echo $time->getDayOfWeek();   // 6 - but may vary based on locale's starting day of the week
echo $time->getDayOfYear();   // 225
echo $time->getWeekOfMonth(); // 2
echo $time->getWeekOfYear();  // 33
echo $time->getTimestamp();   // 1471018523 - UNIX timestamp
echo $time->getQuarter();     // 3

echo $time->dayOfWeek;   // 6
echo $time->dayOfYear;   // 225
echo $time->weekOfMonth; // 2
echo $time->weekOfYear;  // 33
echo $time->timestamp;   // 1471018523
echo $time->quarter;     // 3
