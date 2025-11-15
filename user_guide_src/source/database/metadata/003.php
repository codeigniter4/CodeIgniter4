<?php

$db = db_connect();

$fields = $db->getFieldNames('table_name');

foreach ($fields as $field) {
    echo $field;
}
