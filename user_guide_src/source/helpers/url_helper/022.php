<?php

// The route is defined as:
$routes->get('pages/(:segment)', 'Page::index/$1');

?>

<a href="<?= url_to('Page::index', 'home') ?>">Home</a>
<!-- Result: 'http://example.com/pages/home' -->
