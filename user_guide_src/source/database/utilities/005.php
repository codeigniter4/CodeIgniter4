<?php

$dbutil = \Config\Database::utils();

if ($dbutil->databaseExists('database_name')) {
    // some code...
}
