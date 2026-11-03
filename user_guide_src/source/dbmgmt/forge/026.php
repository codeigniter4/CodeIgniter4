<?php

$fields = [
    'old_name' => [
        'name' => 'new_name',
        'type' => 'TEXT',
        'null' => false,
    ],
];
$forge->modifyColumn('table_name', $fields);
// gives ALTER TABLE `table_name` CHANGE `old_name` `new_name` TEXT NOT NULL
