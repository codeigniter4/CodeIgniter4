<?php

$routes->get('products/([a-z]+)/(\d+)', 'Products::show/$1/id_$2');
