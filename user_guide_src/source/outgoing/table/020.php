<?php

$data = [
    [
        'color' => 'Blue',
        'name'  => 'Fred',
        'size'  => 'Small',
    ],
    [
        'size' => 'Large',
        'age'  => '24',
        'name' => 'Mary',
    ],
    [
        'color' => 'Green',
    ],
];

$table = new \CodeIgniter\View\Table();

$table->setHeading(['name' => 'Name', 'color' => 'Color', 'size' => 'Size'])
    ->setSyncRowsWithHeading(true);

echo $table->generate($data);
