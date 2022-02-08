<?php

$files->removeFile(APPPATH . 'Filters/DevelopToolbar');

$files->removePattern('#\.gitkeep#');
$files->retainPattern('*.php');
