<?php

$length = $benchmark->record('string length', static fn () => strlen('CI4'));

/*
 * $length = 3
 *
 * Same as:
 *
 * $benchmark->start('string length');
 * $length = strlen('CI4');
 * $benchmark->stop('string length');
*/
