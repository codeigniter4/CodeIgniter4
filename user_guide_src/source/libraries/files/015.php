<?php

$files = new FileCollection();
$files->add(APPPATH . 'Config', true); // Adds all Config files and directories

$files->removePattern('*tion.php'); // Would remove Encryption.php, Validation.php, and boot/production.php
$files->removePattern('*tion.php', APPPATH . 'Config/boot'); // Would only remove boot/production.php

$files->retainPattern('#A.+php$#'); // Would keep only Autoload.php
$files->retainPattern('#d.+php$#', APPPATH . 'Config/boot'); // Would keep everything but boot/production.php and boot/testing.php
