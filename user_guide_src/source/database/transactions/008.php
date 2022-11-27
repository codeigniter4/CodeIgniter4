<?php

// When DBDebug in the Database Config is true.

use CodeIgniter\Database\Exceptions\DatabaseException;

try {
    $this->db->transStart();
    $this->db->query('AN SQL QUERY...');
    $this->db->query('ANOTHER QUERY...');
    $this->db->query('AND YET ANOTHER QUERY...');
    $this->db->transComplete();
} catch (DatabaseException $e) {
    // Automatically rolled back already.
}
