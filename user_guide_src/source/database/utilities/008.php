<?php

$dbutil = \Config\Database::utils();

$result = $dbutil->optimizeDatabase();

if ($result !== false) {
    print_r($result);
}
