<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('2024-01-01 12:30:00', 'UTC');

$time->between('2024-01-01 12:00:00', '2024-01-01 13:00:00');        // true
$time->between('2024-01-01 13:00:00', '2024-01-01 12:00:00');        // true
$time->between('2024-01-01 13:00:00', '2024-01-01 14:00:00', true, 'Europe/Warsaw'); // true
$time->between('2024-01-01 12:30:00', '2024-01-01 13:00:00', false); // false
