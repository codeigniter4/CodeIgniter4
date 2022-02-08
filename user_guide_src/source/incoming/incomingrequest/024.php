<?php

$files = $request->getFiles();

// Grab the file by name given in HTML form
if ($files->hasFile('userfile')) {
    $file = $files->getFile('userfile');

    // Generate a new secure name
    $name = $file->getRandomName();

    // Move the file to it's new home
    $file->move('/path/to/dir', $name);

    echo $file->getSize('mb'); // 1.23
    echo $file->getExtension(); // jpg
    echo $file->getType(); // image/jpg
}
