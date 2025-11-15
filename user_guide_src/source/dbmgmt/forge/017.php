<?php

// Produces: DROP TABLE `table_name`
$forge->dropTable('table_name');

// Produces: DROP TABLE IF EXISTS `table_name`
$forge->dropTable('table_name', true);
