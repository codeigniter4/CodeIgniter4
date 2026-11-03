<?php

use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;

try {
    $db->table('users')->insert(['email' => 'duplicate@example.com']);
} catch (UniqueConstraintViolationException $e) {
    // Handle duplicate key violation
}
