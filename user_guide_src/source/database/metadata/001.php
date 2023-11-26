<?php

$db = db_connect();

$tables = $db->listTables();

foreach ($tables as $table) {
    echo $table;
}
