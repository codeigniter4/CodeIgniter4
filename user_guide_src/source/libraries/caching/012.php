<?php

use CodeIgniter\Cache\Handlers\BaseHandler;

$prefixedKey = BaseHandler::validateKey($key, $prefix);
