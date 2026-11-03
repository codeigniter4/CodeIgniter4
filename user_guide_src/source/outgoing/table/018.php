<?php

$table = new \CodeIgniter\View\Table();

$table->setCaption('Preferences')
    ->setHeading('Name', 'Color', 'Size')
    ->addRow('Fred', 'Blue', 'Small')
    ->addRow('Mary', 'Red', 'Large')
    ->addRow('John', 'Green', 'Medium');

echo $table->generate();

$table->clear();

$table->setCaption('Shipping')
    ->setHeading('Name', 'Day', 'Delivery')
    ->addRow('Fred', 'Wednesday', 'Express')
    ->addRow('Mary', 'Monday', 'Air')
    ->addRow('John', 'Saturday', 'Overnight');

echo $table->generate();
