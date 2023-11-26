<?php

$sql = 'SELECT * FROM some_table WHERE id = :id: AND status = :status: AND author = :name:';
$db->query($sql, [
    'id'     => 3,
    'status' => 'live',
    'name'   => 'Rick',
]);
