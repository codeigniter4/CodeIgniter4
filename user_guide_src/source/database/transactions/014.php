<?php

$this->db->transaction(static function ($db) use ($temporaryPath, $record): void {
    $db->afterRollback(static function () use ($temporaryPath): void {
        if (is_file($temporaryPath)) {
            unlink($temporaryPath);
        }
    });

    $db->table('documents')->insert($record);
});
