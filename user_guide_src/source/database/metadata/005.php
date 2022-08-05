<?php

$db = db_connect();

if ($db->fieldExists('field_name', 'table_name')) {
    // some code...
}
