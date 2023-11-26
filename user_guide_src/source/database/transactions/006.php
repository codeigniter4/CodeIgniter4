<?php

$this->db->transBegin();

$this->db->query('AN SQL QUERY...');
$this->db->query('ANOTHER QUERY...');
$this->db->query('AND YET ANOTHER QUERY...');

if ($this->db->transStatus() === false) {
    $this->db->transRollback();
} else {
    $this->db->transCommit();
}
