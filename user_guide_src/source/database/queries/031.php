<?php

use CodeIgniter\Database\Exceptions\ConstraintViolationException;

$inserted = $db->table('users')->insert(['email' => 'duplicate@example.com']);

if (! $inserted && $db->getLastException() instanceof ConstraintViolationException) {
    // Handle the constraint violation.
}
