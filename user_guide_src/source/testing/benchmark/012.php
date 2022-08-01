<?php

$length = timer('string length', static fn () => strlen('CI4'));

/*
 * $length = 3
 *
 * Same as:
 *
 * timer('string length');
 * $length = strlen('CI4');
 * timer('string length');
*/
