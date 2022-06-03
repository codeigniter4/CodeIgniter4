<?php

$thead = ['ID', 'Title', 'Updated At', 'Active'];
$tbody = [
    [7, 'A great item title', '2017-11-15 10:35:02', 1],
    [8, 'Another great item title', '2017-11-16 13:46:54', 0],
];

CLI::table($tbody, $thead);
