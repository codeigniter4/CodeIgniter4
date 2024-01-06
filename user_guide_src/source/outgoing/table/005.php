<?php

$table = new \CodeIgniter\View\Table();

$table->setHeading(['Name', 'Color', 'Size']);

$table->addRow(['Fred', 'Blue', 'Small']);
$table->addRow(['Mary', 'Red', 'Large']);
$table->addRow(['John', 'Green', 'Medium']);

echo $table->generate();
