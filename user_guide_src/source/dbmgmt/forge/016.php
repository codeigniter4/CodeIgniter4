<?php

$attributes = ['ENGINE' => 'InnoDB'];
$forge->createTable('table_name', false, $attributes);
// produces: CREATE TABLE `table_name` (...) ENGINE = InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
