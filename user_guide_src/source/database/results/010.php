<?php

$query = $db->query('YOUR QUERY');

while ($row = $query->getUnbufferedRow()) {
    echo $row->title;
    echo $row->name;
    echo $row->body;
}
