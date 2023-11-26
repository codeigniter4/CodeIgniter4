<?php

$timer = new Timer();
$timer->start('longjohn', strtotime('-11 minutes'));
$this->assertCloseEnoughString(11 * 60, $timer->getElapsedTime('longjohn'));
