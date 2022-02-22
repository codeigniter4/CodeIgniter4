<?php

$table = new \CodeIgniter\View\Table();

$table->setHeading('Name', 'Color', 'Size');
$table->addRow('Fred', '<strong>Blue</strong>', 'Small');

$table->function = 'htmlspecialchars';
echo $table->generate();
