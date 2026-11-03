<?php

// Get the file's basename
echo $file->getBasename();
// Get last modified time
echo $file->getMTime();
// Get the true real path
echo $file->getRealPath();
// Get the file permissions
echo $file->getPerms();

// Write CSV rows to it.
if ($file->isWritable()) {
    $csv = $file->openFile('w');

    foreach ($rows as $row) {
        $csv->fputcsv($row);
    }
}
