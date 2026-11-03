<?php

$iterator = new \CodeIgniter\Debug\Iterator();

$iterator->add('double', static function ($word = 'little') {
    "Some basic {$word} string test.";
});
