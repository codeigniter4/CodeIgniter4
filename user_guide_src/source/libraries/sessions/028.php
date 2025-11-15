<?php

// Both 'item' and 'item2' will expire after 300 seconds
$session->markAsTempdata(['item', 'item2'], 300);

// 'item' will be erased after 300 seconds, while 'item2'
// will do so after only 240 seconds
$session->markAsTempdata([
    'item'  => 300,
    'item2' => 240,
]);
