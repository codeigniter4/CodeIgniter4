<?php

use CodeIgniter\Database\Exceptions\ConstraintViolationException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\ForeignKeyConstraintViolationException;
use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;

try {
    $db->table('users')->insert(['email' => 'duplicate@example.com']);
} catch (UniqueConstraintViolationException $e) {
    // Handle duplicate key violation.
} catch (ForeignKeyConstraintViolationException $e) {
    // Handle missing or referenced parent row.
} catch (ConstraintViolationException $e) {
    // Handle another known database constraint violation.
} catch (DatabaseException $e) {
    // Handle another database error.
}
