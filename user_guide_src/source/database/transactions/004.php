<?php

$this->db->transOff();
$this->db->transStart();
$this->db->query('AN SQL QUERY...');
$this->db->transComplete();
