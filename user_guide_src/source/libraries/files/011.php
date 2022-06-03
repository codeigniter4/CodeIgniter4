<?php

$files = new FileCollection([
    FCPATH . 'index.php',
    ROOTPATH . 'spark',
]);
$files->addDirectory(APPPATH . 'Filters');
