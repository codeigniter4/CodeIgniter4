<?php

$iterator = new \CodeIgniter\Benchmark\Iterator();

// Add a new task
$iterator->add('single_concat', function () {
    $str = 'Some basic'.'little'.'string concatenation test.';
});

// Add another task
$iterator->add('double', function ($a = 'little') {
    $str = "Some basic {$little} string test.";
});
