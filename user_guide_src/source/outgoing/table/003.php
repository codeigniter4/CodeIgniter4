<?php

$table = new \CodeIgniter\View\Table();

$query = $db->query('SELECT * FROM my_table');

echo $table->generate($query);
