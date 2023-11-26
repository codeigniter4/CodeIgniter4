<?php

$publisher = new Publisher('/home/source', '/home/destination');
$publisher->addPaths([
    'pencil/lead.png',
    'metal/lead.png',
]);

// Results in "/home/destination/pencil/lead.png" and "/home/destination/metal/lead.png"
$publisher->merge();
