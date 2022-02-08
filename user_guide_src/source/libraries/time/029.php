<?php

$time  = Time::parse('13 May 2020 10:00', 'America/Chicago');
$time2 = $time->setTimezone('Europe/London'); // Returns new instance converted to new timezone

echo $time->getTimezoneName();  // American/Chicago
echo $time2->getTimezoneName(); // Europe/London

echo $time->toDateTimeString();  // 2020-05-13 10:00:00
echo $time2->toDateTimeString(); // 2020-05-13 18:00:00
