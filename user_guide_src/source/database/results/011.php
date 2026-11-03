<?php

$db->resultMode = MYSQLI_USE_RESULT; // for unbuffered results

$query = $db->query('YOUR QUERY');

$file = new \CodeIgniter\Files\File(WRITEPATH . 'data.csv');

$csv = $file->openFile('w');

while ($row = $query->getUnbufferedRow('array')) {
    $csv->fputcsv($row);
}

$db->resultMode = MYSQLI_STORE_RESULT; // return to default mode
