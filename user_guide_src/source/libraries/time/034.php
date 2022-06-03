<?php

$time1 = Time::parse('January 10, 2017 21:50:00', 'America/Chicago');
$time2 = Time::parse('January 11, 2017 03:50:00', 'Europe/London');

$time1->sameAs($time2); // false
$time2->sameAs('January 10, 2017 21:50:00', 'America/Chicago'); // true
