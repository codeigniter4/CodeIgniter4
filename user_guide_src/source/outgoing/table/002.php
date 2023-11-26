<?php

$table = new \CodeIgniter\View\Table();

$data = [
    ['Name', 'Color', 'Size'],
    ['Fred', 'Blue', 'Small'],
    ['Mary', 'Red', 'Large'],
    ['John', 'Green', 'Medium'],
];

echo $table->generate($data);
