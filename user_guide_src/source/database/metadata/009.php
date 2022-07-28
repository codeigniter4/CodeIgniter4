<?php

$db = db_connect();

$keys = $db->getForeignKeyData('table_name');

foreach ($keys as $key) {
    echo $key->constraint_name;
    echo $key->table_name;
    echo $key->column_name;
    echo $key->foreign_table_name;
    echo $key->foreign_column_name;
}
