<?php

$this->db->afterCommit(static function (): void {
    // Runs immediately because there is no active transaction.
});
