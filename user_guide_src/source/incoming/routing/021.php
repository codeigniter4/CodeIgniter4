<?php

$multipleRoutes = [
    'product/(:num)'      => 'Catalog::productLookupById',
    'product/(:alphanum)' => 'Catalog::productLookupByName',
];

$routes->map($multipleRoutes);
