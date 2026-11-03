<?php

// Find by email, or insert with additional data.
$user = $userModel->firstOrInsert(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'country' => 'US'],
);

// The above will trigger:
//
// 1) First it tries to find the record:
// SELECT * FROM `users` WHERE `email` = 'john@example.com' LIMIT 1;
//
// 2) If no result is found, it inserts a new record:
// INSERT INTO `users` (`email`, `name`, `country`)
// VALUES ('john@example.com', 'John Doe', 'US');
//
// 3) Then it returns the found or newly created entity/row,
// or false if something went wrong
