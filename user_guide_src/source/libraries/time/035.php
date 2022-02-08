<?php

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'America/Chicago');

$time1->isBefore($time2); // true
$time2->isBefore($time1); // false
