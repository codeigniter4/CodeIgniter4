<?php

$tables = $db->listTables();

foreach ($tables as $table) {
    echo $table;
}
