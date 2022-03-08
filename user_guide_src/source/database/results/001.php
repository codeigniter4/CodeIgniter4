<?php

$query = $db->query('YOUR QUERY');

foreach ($query->getResult() as $row) {
    echo $row->title;
    echo $row->name;
    echo $row->body;
}
