<?php

use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;

$inserted = $db->table('users')->insert(['email' => 'duplicate@example.com']);

if (! $inserted && $db->getLastException() instanceof UniqueConstraintViolationException) {
    // Handle duplicate key violation
}
