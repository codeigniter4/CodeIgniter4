<?php

$dbutil = \CodeIgniter\Database\Config::utils();

if ($dbutil->optimizeTable('table_name')) {
    echo 'Success!';
}
