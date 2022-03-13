<?php

$_SESSION['item'] = 'value';
$session->markAsTempdata('item', 300); // Expire in 5 minutes
