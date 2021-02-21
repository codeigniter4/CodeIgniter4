<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Config\Services;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;
use Config\Autoload;
use Config\Modules;

// Initialize the autoloader.
Services::autoloader()->initialize(new Autoload(), new Modules());

/*
 * ---------------------------------------------------------------
 * GRAB OUR CODEIGNITER INSTANCE
 * ---------------------------------------------------------------
 *
 * The CodeIgniter class contains the core functionality to make
 * the application run, and does all of the dirty work to get
 * the pieces all working together.
 */

$app = new MockCodeIgniter(new App());
$app->initialize();

return $app;
