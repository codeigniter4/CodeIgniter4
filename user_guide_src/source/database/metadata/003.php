<?php

$fields = $db->getFieldNames('table_name');

foreach ($fields as $field) {
    echo $field;
}
