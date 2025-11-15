<?php

$throttler = service('throttler');
$throttler->check($name, 60, MINUTE);
