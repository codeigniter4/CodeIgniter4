<?php

$routes->get('product/(:num)', 'Catalog::productLookupByID/$1');
