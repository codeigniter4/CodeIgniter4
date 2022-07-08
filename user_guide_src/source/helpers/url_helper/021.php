<?php

// The route is defined as:
$routes->get('/', 'Home::index');

?>

<a href="<?= url_to('Home::index') ?>">Home</a>
<!-- Result: 'http://example.com/' -->

