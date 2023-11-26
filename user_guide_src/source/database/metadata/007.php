<?php

$db = db_connect();

$query  = $db->query('YOUR QUERY');
$fields = $query->getFieldData();
