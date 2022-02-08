<?php

$lunch  = Time::createFromTime(11, 30);     // 11:30 am today
$dinner = Time::createFromTime(18, 00, 00); // 6:00 pm today
$time   = Time::createFromTime($hour, $minutes, $seconds, $timezone, $locale);
