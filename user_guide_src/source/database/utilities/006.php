<?php

$dbutil = \Config\Database::utils();

if ($dbutil->optimizeTable('table_name')) {
    echo 'Success!';
}
