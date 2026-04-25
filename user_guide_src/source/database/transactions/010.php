<?php

$this->db->transStart();
$this->db->query('AN SQL QUERY...');

$this->db->afterCommit(static function (): void {
    // Dispatch a queued job or run another side effect after commit.
});

$this->db->afterRollback(static function (): void {
    // Run cleanup that should only happen after rollback.
});

$this->db->transComplete();
