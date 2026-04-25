<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('April 25, 2026 5:46:00pm UTC');

// If the time is between the two times, it will return the time itself.
echo $time->clamp('April 25, 2026 5:00:00pm UTC', 'April 25, 2026 7:00:00pm UTC')->toDateTimeString(); // 2026-04-25 17:46:00

// If the time is before the start time, it will return new instance of start time.
echo $time->clamp('April 25, 2026 6:05:00pm UTC', 'April 25, 2026 9:20:00pm UTC')->toDateTimeString(); // 2026-04-25 18:05:00

// If the time is after the end time, it will return new instance of end time.
echo $time->clamp('April 25, 2026 3:40:00pm UTC', 'April 25, 2026 5:00:00pm UTC')->toDateTimeString(); // 2026-04-25 17:00:00
