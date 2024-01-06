<?php

$throttler = \Config\Services::throttler();
$throttler->check($name, 60, MINUTE);
