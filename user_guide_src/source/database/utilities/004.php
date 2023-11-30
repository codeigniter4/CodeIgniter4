<?php

$dbutil = \CodeIgniter\Database\Config::utils();

$dbs = $dbutil->listDatabases();

foreach ($dbs as $db) {
    echo $db;
}
