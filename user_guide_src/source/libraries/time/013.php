<?php

use CodeIgniter\I18n\Time;

$dt   = new \DateTime('now');
$time = Time::createFromInstance($dt, 'en_US');
