<?php

$forge->addForeignKey('users_id', 'users', 'id', 'CASCADE', 'CASCADE');
// gives CONSTRAINT `TABLENAME_users_foreign` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE

$forge->addForeignKey(['users_id', 'users_name'], 'users', ['id', 'name'], 'CASCADE', 'CASCADE');
// gives CONSTRAINT `TABLENAME_users_foreign` FOREIGN KEY(`users_id`, `users_name`) REFERENCES `users`(`id`, `name`) ON DELETE CASCADE ON UPDATE CASCADE
