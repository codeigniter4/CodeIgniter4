<?php

echo 'My files: ' . implode(PHP_EOL, $files->get());
echo 'I have ' . count($files) . ' files!';

foreach ($files as $file) {
    echo 'Moving ' . $file->getBasename() . ', ' . $file->getSizeByUnit('mb');
    $file->move(WRITABLE . $file->getRandomName());
}
