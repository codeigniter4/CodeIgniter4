<?php

$time = Time::parse('August 12, 2016 4:15:23pm');

echo $time->getYear();   // 2016
echo $time->getMonth();  // 8
echo $time->getDay();    // 12
echo $time->getHour();   // 16
echo $time->getMinute(); // 15
echo $time->getSecond(); // 23

echo $time->year;   // 2016
echo $time->month;  // 8
echo $time->day;    // 12
echo $time->hour;   // 16
echo $time->minute; // 15
echo $time->second; // 23
