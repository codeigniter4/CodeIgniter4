<?php

// Limit to any sub-domain
$routes->get('from', 'to', ['subdomain' => '*']);
