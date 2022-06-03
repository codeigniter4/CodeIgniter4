<?php

$query = $db->table('table_name')->get();

foreach ($query->getResult() as $row) {
    echo $row->title;
}
