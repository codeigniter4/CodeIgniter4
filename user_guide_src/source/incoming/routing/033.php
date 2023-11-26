<?php

$routes->add('from', 'to', $options);
$routes->get('from', 'to', $options);
$routes->post('from', 'to', $options);
$routes->put('from', 'to', $options);
$routes->head('from', 'to', $options);
$routes->options('from', 'to', $options);
$routes->delete('from', 'to', $options);
$routes->patch('from', 'to', $options);
$routes->match(['get', 'put'], 'from', 'to', $options);
$routes->resource('photos', $options);
$routes->map($array, $options);
$routes->group('name', $options, static function () {});
