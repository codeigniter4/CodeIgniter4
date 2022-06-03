<?php

$fields = [
    'preferences' => ['type' => 'TEXT'],
];
$forge->addColumn('table_name', $fields);
// Executes: ALTER TABLE `table_name` ADD `preferences` TEXT
