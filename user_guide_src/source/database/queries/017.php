<?php

use CodeIgniter\Database\Query;

$pQuery = $db->prepare(function ($db) {
    $sql = "INSERT INTO user (name, email, country) VALUES (?, ?, ?)";

    return (new Query($db))->setQuery($sql);
});
