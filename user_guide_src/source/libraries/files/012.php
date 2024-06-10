<?php

$files->removeFile(APPPATH . 'Filters/DevelopToolbar.php');

$files->removePattern('#\.gitkeep#');
$files->retainPattern('*.php');
