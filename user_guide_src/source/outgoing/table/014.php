<?php

$cell = ['data' => 'Blue', 'class' => 'highlight', 'colspan' => 2];
$table->addRow($cell, 'Red', 'Green');

?>

<!-- Generates: -->
<td class='highlight' colspan='2'>Blue</td><td>Red</td><td>Green</td>
