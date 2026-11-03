<?php

$this->db->transStart(); // actually starts a transaction
$this->db->query('SOME QUERY 1 ...');
$this->db->transStart(); // doesn't necessarily start another transaction
$this->db->query('SOME QUERY 2 ...');
$this->db->transComplete(); // doesn't necessarily end the transaction, but required to finish the inner transaction
$this->db->query('SOME QUERY 3 ...');
$this->db->transComplete(); // actually ends the transaction
