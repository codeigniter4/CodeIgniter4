<?php

$benchmark->record('slow_function', function() { slow_function('...'); });

/*
 * Same as:
 * 
 * $benchmark->start('slow_function');
 * slow_function('...');
 * $benchmark->stop('slow_function');
*/