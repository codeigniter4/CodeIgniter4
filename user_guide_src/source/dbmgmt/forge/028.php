<?php

// MySqli Produces: ALTER TABLE `tablename` DROP PRIMARY KEY
// Others Produces: ALTER TABLE `tablename` DROP CONSTRAINT `pk_tablename`
$forge->dropPrimaryKey('tablename');
