<?php

$current = Time::parse('March 10, 2017', 'America/Chicago');
$test    = Time::parse('March 10, 2010', 'America/Chicago');

$diff = $current->difference($test);

echo $diff->getYears();   // -7
echo $diff->getMonths();  // -84
echo $diff->getWeeks();   // -365
echo $diff->getDays();    // -2557
echo $diff->getHours();   // -61368
echo $diff->getMinutes(); // -3682080
echo $diff->getSeconds(); // -220924800
