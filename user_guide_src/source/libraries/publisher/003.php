<?php

use CodeIgniter\Publisher\Publisher;

$vendorPublisher = new Publisher(ROOTPATH . 'vendor');
$filterPublisher = new Publisher('/path/to/module/Filters', APPPATH . 'Filters');

// Once the source and destination are set you may start adding relative input files
$frameworkPublisher = new Publisher(ROOTPATH . 'vendor/codeigniter4/codeigniter4');

// All "path" commands are relative to $source
$frameworkPublisher->addPath('app/Config/Cookie.php');

// You may also add from outside the source, but the files will not be merged into subdirectories
$frameworkPublisher->addFiles([
    '/opt/mail/susan',
    '/opt/mail/ubuntu',
]);
$frameworkPublisher->addDirectory(SUPPORTPATH . 'Images');
