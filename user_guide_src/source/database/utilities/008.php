<?php

$dbutil = \CodeIgniter\Database\Config::utils();

$result = $dbutil->optimizeDatabase();

if ($result !== false) {
    print_r($result);
}
