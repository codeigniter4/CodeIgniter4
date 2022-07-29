<?php

$db = db_connect();

$query = $db->query('SELECT * FROM some_table');

foreach ($query->getFieldNames() as $field) {
    echo $field;
}
