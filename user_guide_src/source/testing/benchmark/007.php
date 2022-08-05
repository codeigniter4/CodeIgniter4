<?php

$iterator = new \CodeIgniter\Debug\Iterator();

// Add a new task
$iterator->add('single_concat', static function () {
    $str = 'Some basic' . 'little' . 'string concatenation test.';
});

// Add another task
$iterator->add('double', static function ($a = 'little') {
    $str = "Some basic {$little} string test.";
});
