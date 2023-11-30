<?php

$dbutil = \CodeIgniter\Database\Config::utils();

if ($dbutil->repairTable('table_name')) {
    echo 'Success!';
}
