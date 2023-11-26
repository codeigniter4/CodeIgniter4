<?php

$forge->dropColumn('table_name', 'column_1,column_2'); // by proving comma separated column names
$forge->dropColumn('table_name', ['column_1', 'column_2']); // by proving array of column names
