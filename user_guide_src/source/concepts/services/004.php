<?php

$renderer = service('renderer', APPPATH . 'views/');

// The code above is the same as the code below.
$renderer = \Config\Services::renderer(APPPATH . 'views/');
