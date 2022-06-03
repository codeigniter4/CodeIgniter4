<?php

$this->db->transStart(true); // Query will be rolled back
$this->db->query('AN SQL QUERY...');
$this->db->transComplete();
