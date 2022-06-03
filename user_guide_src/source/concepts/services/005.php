<?php

$logger = single_service('logger');

// The code above is the same as the code below.
$logger = \Config\Services::logger(false);
