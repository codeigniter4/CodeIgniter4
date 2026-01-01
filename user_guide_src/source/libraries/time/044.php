<?php

use CodeIgniter\I18n\Time;

$time = Time::parse('tomorrow');
echo $time->isFuture(); // true
