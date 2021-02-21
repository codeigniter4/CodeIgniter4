<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CodeIgniter\Config\DotEnv;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use Config\App;

// Now load Composer's if it's available
if (is_file(COMPOSER_PATH))
{
	/*
	 * The path to the vendor directory.
	 *
	 * We do not want to enforce this, so set the constant if Composer was used.
	 */
	if (! defined('VENDORPATH'))
	{
		define('VENDORPATH', realpath(ROOTPATH . 'vendor') . DIRECTORY_SEPARATOR);
	}

	require_once COMPOSER_PATH;
}

// Load environment settings from .env files into $_SERVER and $_ENV
require_once SYSTEMPATH . 'Config/DotEnv.php';

$env = new DotEnv(ROOTPATH);
$env->load();

// Always load the URL helper, it should be used in most of apps.
helper('url');

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
