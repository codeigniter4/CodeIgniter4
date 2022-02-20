<?php

$routes->get('product/(:segment)', 'Catalog::productLookup/$1');
