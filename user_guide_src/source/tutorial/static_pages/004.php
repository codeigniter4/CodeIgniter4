<?php

$routes->get('pages', 'Pages::index');
$routes->get('(:any)', 'Pages::view/$1');
