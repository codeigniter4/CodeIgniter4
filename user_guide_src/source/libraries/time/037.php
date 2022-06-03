<?php

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

$time1->isAfter($time2); // false
$time2->isAfter($time1); // true
