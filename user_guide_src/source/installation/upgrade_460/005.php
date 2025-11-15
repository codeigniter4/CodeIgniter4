<?php

use CodeIgniter\I18n\Time;

$time1 = new Time('2024-01-01 12:00:00');
echo $time1->getTimestamp(); // 1704110400

$time2 = new Time('2024-01-01 12:00:00.654321');
echo $time2->getTimestamp(); // 1704110400
