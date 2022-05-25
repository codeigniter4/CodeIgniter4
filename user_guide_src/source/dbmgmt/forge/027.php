<?php

use CodeIgniter\Database\RawSql;

$fields = [
    'id' => [
        'type'           => 'INT',
        'constraint'     => 5,
        'unsigned'       => true,
        'auto_increment' => true,
    ],
    'created_at' => [
        'type'    => 'TIMESTAMP',
        'default' => new RawSql('CURRENT_TIMESTAMP'),
    ],
];
$forge->addField($fields);
/*
gives:
    "id" INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
*/
