<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/*
 * System URI Routing
 *
 * This file contains any routing to system tools, such as command-line
 * tools for migrations, etc.
 *
 * It is called by Config\Routes, and has the $routes RouteCollection
 * already loaded up and ready for us to use.
 */

// CLI Catchall - uses a _remap to call Commands
$routes->cli('ci(:any)', '\CodeIgniter\CLI\CommandRunner::index/$1');
