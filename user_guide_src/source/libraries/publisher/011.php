<?php

$publisher = new Publisher('/home/source', '/home/destination');
$publisher->addPaths([
    'pencil/lead.png',
    'metal/lead.png',
]);

// This is bad! Only one file will remain at /home/destination/lead.png
$publisher->copy(true);
