<?php

// When DBDebug in the Database Config must be true.

use CodeIgniter\Database\Exceptions\DatabaseException;

try {
    $this->db->transException(true)->transStart();
    $this->db->query('AN SQL QUERY...');
    $this->db->query('ANOTHER QUERY...');
    $this->db->query('AND YET ANOTHER QUERY...');
    $this->db->transComplete();
} catch (DatabaseException $e) {
    // Automatically rolled back already.
}
