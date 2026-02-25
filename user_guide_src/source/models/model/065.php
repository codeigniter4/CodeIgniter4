<?php

// Find by email, or insert with additional data.
$user = $userModel->firstOrInsert(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe', 'country' => 'US'],
);
