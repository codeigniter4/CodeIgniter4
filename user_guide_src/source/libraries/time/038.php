<?php

$time = Time::parse('March 10, 2017', 'America/Chicago');

$diff = $time->difference(Time::now());
$diff = $time->difference(new DateTime('July 4, 1975', 'America/Chicago'));
$diff = $time->difference('July 4, 1975 13:32:05', 'America/Chicago');
