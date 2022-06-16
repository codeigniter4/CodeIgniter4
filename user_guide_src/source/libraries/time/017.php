<?php

// Locale: en
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toDateString(); // 2016-03-09

// Locale: fa
$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toDateString(); // ۲۰۱۶-۰۳-۰۹
