<?php

$db->table('users')->where('status', \UserStatus::Active)->get();

$db->query(
    'SELECT * FROM users WHERE status = ?',
    [\UserStatus::Active],
);
