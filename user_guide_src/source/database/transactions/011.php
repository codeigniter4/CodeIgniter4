<?php

$this->db->afterCommit(static function (): void {
    // Runs immediately because no transaction has started yet.
});

$this->db->transStart();
$this->db->query('AN SQL QUERY...');
$this->db->transComplete();
