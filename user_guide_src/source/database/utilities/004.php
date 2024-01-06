<?php

$dbutil = \Config\Database::utils();

$dbs = $dbutil->listDatabases();

foreach ($dbs as $db) {
    echo $db;
}
