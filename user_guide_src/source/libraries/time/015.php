<?php

$time = Time::parse('March 9, 2016 12:00:00', 'America/Chicago');
echo $time->toLocalizedString('MMM d, yyyy'); // March 9, 2016
