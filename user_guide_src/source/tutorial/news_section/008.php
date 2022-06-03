<?php

// ...

$routes->get('news/(:segment)', 'News::view/$1');
$routes->get('news', 'News::index');
$routes->get('pages', 'Pages::index');
$routes->get('(:any)', 'Pages::view/$1');

// ...
