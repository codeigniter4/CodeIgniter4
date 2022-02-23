<?php

$attributes = ['ENGINE' => 'InnoDB'];
$forge->createTable('table_name', false, $attributes);
// produces: CREATE TABLE `table_name` (...) ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
