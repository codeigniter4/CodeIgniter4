<?php

$time  = Time::parse('May 10, 2017', 'America/Chicago');
$time2 = $time->setTimestamp(strtotime('April 1, 2017'));

echo $time->toDateTimeString();  // 2017-05-10 00:00:00
echo $time2->toDateTimeString(); // 2017-04-01 00:00:00
