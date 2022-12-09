<?php

$routes->get('pages', 'Pages::index');
$routes->get('(:segment)', 'Pages::view/$1');
