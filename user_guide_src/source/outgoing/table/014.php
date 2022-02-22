<?php

$cell = ['data' => 'Blue', 'class' => 'highlight', 'colspan' => 2];
$table->addRow($cell, 'Red', 'Green');

// generates
// <td class='highlight' colspan='2'>Blue</td><td>Red</td><td>Green</td>
