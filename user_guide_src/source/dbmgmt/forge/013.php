<?php

$forge->addForeignKey('users_id', 'users', 'id', 'CASCADE', 'CASCADE', 'my_fk_name');
// gives CONSTRAINT `my_fk_name` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

$forge->addForeignKey('users_id', 'users', 'id', '', 'CASCADE');
// gives CONSTRAINT `TABLENAME_users_foreign` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE

$forge->addForeignKey(['users_id', 'users_name'], 'users', ['id', 'name'], 'CASCADE', 'CASCADE', 'my_fk_name');
// gives CONSTRAINT `my_fk_name` FOREIGN KEY(`users_id`, `users_name`) REFERENCES `users`(`id`, `name`) ON DELETE CASCADE ON UPDATE CASCADE
