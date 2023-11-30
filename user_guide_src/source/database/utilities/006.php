<?php

$dbutil = \CodeIgniter\Database\Config::utils();

if ($dbutil->optimize_table('table_name')) {
    echo 'Success!';
}
