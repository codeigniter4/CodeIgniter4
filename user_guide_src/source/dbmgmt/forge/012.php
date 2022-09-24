<?php

$forge->addForeignKey('users_id', 'users', 'id');
// gives CONSTRAINT `TABLENAME_users_id_foreign` FOREIGN KEY(`users_id`) REFERENCES `users`(`id`)

$forge->addForeignKey(['users_id', 'users_name'], 'users', ['id', 'name']);
// gives CONSTRAINT `TABLENAME_users_id_foreign` FOREIGN KEY(`users_id`, `users_name`) REFERENCES `users`(`id`, `name`)
