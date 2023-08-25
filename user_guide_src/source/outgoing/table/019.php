<?php

$table = new \CodeIgniter\View\Table();

$table->setHeading(['name' => 'Name', 'color' => 'Color', 'size' => 'Size'])
    ->setSyncRowsWithHeading(true)
    ->addRow(['color' => 'Blue', 'name' => 'Fred', 'size' => 'Small'])
    ->addRow(['size' => 'Large', 'age' => '24', 'name' => 'Mary'])
    ->addRow(['color' => 'Green']);

echo $table->generate();
?>

<!-- Generates a table with this prototype: -->
<table border="0" cellpadding="4" cellspacing="0">
    <thead>
        <tr>
            <th>Name</th>
            <th>Color</th>
            <th>Size</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fred</td>
            <td>Blue</td>
            <td>Small</td>
        </tr>
        <tr>
            <td>Mary</td>
            <td></td>
            <td>Large</td>
        </tr>
        <tr>
            <td></td>
            <td>Green</td>
            <td></td>
        </tr>
    </tbody>
</table>
