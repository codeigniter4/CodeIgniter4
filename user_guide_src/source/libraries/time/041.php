<?php

$current = Time::parse('March 10, 2017', 'America/Chicago');
$test    = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');

$diff = $current->difference($test);

echo $diff->humanize(); // 1 year ago
