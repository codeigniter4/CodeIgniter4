<?php

/*
 * For errors:
 * [
 *     'foo.0.bar'   => 'Error',
 *     'foo.baz.bar' => 'Error',
 * ]
 */

// returns true
$validation->hasError('foo.*.bar');
