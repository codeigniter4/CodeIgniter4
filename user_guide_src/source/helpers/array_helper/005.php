<?php

$data = [
    'foo' => [
        'bar.baz' => 23,
    ],
    'foo.bar' => [
        'baz' => 43,
    ],
];

// Returns: 23
$barBaz = dot_array_search('foo.bar\.baz', $data);
// Returns: 43
$fooBar = dot_array_search('foo\.bar.baz', $data);
