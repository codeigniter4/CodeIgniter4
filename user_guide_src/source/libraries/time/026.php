<?php

$tz = Time::now()->getTimezone();
$tz = Time::now()->timezone;

echo $tz->getName();
echo $tz->getOffset();
