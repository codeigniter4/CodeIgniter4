<?php

$dbutil = \CodeIgniter\Database\Config::utils();

if ($dbutil->databaseExists('database_name')) {
    // some code...
}
