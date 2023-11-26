<?php

$today       = Time::createFromDate();     // Uses current year, month, and day
$anniversary = Time::createFromDate(2018); // Uses current month and day
$date        = Time::createFromDate(2018, 3, 15, 'America/Chicago', 'en_US');
