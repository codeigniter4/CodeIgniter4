<?php

use CodeIgniter\I18n\Time;

$current = Time::parse('2024-03-31', 'Europe/Madrid');
$test    = Time::parse('2024-04-01', 'Europe/Madrid');

$diff = $current->difference($test);

echo $diff->getDays(); // 0
