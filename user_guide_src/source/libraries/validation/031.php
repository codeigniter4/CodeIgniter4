<?php

// for errors
[
    'foo.0.bar'   => 'Error',
    'foo.baz.bar' => 'Error',
];

$validation->hasError('foo.*.bar'); // return true
