<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('yesterday');
echo $time->isPast(); // true
