<?php

// For Indexes Produces:        DROP INDEX `users_index` ON `tablename`
// For Unique Indexes Produces: ALTER TABLE `tablename` DROP CONSTRAINT `users_index`
$forge->dropKey('tablename', 'users_index', false);
