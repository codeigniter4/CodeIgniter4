<?php

$db = db_connect();

$fields = $db->getFieldData('table_name');

foreach ($fields as $field) {
    echo $field->name;
    echo $field->type;
    echo $field->max_length;
    echo $field->primary_key;
}
