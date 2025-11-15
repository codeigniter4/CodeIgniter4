<?php

// Will place the new column after the `another_field` column:
$fields = [
    'preferences' => ['type' => 'TEXT', 'after' => 'another_field'],
];

// Will place the new column at the start of the table definition:
$fields = [
    'preferences' => ['type' => 'TEXT', 'first' => true],
];
