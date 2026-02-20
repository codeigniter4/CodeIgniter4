<?php

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;

try {
    $db->table('users')->insert(['email' => 'duplicate@example.com']);
} catch (UniqueConstraintViolationException $e) {
    // Duplicate key — handle gracefully
} catch (DatabaseException $e) {
    // Other database error
}
