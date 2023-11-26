<?php

$db = db_connect();

$keys = $db->getForeignKeyData('table_name');

foreach ($keys as $key => $object) {
    echo $key === $object->constraint_name;
    echo $object->constraint_name;
    echo $object->table_name;
    echo $object->column_name[0]; // array
    echo $object->foreign_table_name;
    echo $object->foreign_column_name[0]; // array
    echo $object->on_delete;
    echo $object->on_update;
    echo $object->match;
}
