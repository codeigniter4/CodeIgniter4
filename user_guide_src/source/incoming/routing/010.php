<?php

$routes->get('product/(:any)', 'Catalog::productLookup/$1');
