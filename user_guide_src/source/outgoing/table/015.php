<?php

$list = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve'];

$newList = $table->makeColumns($list, 3);

$table->generate($newList);

?>

<!-- Generates a table with this prototype: -->
<table border="0" cellpadding="4" cellspacing="0">
    <tr>
        <td>one</td>
        <td>two</td>
        <td>three</td>
    </tr>
    <tr>
        <td>four</td>
        <td>five</td>
        <td>six</td>
    </tr>
    <tr>
        <td>seven</td>
        <td>eight</td>
        <td>nine</td>
    </tr>
    <tr>
        <td>ten</td>
        <td>eleven</td>
        <td>twelve</td>
    </tr>
</table>
