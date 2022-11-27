<?php

$this->db->transStart();
$this->db->query('AN SQL QUERY...');
$this->db->query('ANOTHER QUERY...');
$this->db->transComplete();

if ($this->db->transStatus() === false) {
    // generate an error... or use the log_message() function to log your error
}
