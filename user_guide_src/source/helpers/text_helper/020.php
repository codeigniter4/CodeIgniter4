<?php

$string = 'Here is a simple string of text that will help us demonstrate this function.';
echo word_wrap($string, 25);
/*
 * Would produce:
 * Here is a simple string
 * of text that will help us
 * demonstrate this
 * function.
 *
 * Excessively long words will be split, but URLs will not be.
 */
