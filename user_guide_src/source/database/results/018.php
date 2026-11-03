<?php

$query = $db->query('SELECT * FROM my_table');

echo $query->getFieldNames();
