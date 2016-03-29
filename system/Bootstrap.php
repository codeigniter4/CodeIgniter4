<?php namespace CodeIgniter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 4.0.0
 * @filesource
 */

use Config\App;
use Config\Services;
use Config\Autoload;

/**
 * Class Bootstrap
 *
 * @codeCoverageIgnore
 * @package CodeIgniter
 */
class Bootstrap
{
	/**
	 * The application configuration object.
	 *
	 * @var \Config\App
	 */
	protected $config;

	//--------------------------------------------------------------------

	public function __construct(App $config)
	{
		$this->config = $config;

		require_once BASEPATH.'Common.php';

		$this->setExceptionHandling();
		$this->loadComposerAutoloader();
	}
	
	//--------------------------------------------------------------------

	/**
	 * Load any environment-specific settings from .env file
	 */
	protected function loadDotEnv()
	{
		// Load environment settings from .env files
		// into $_SERVER and $_ENV
		require BASEPATH.'Config/DotEnv.php';
		$env = new DotEnv(APPPATH);
		$env->load();
		unset($env);
	}

	//--------------------------------------------------------------------

	/**
	 * Set custom exception handling
	 */
	protected function setExceptionHandling()
	{
		Services::exceptions($this->config, true)
			->initialize();
	}

	//--------------------------------------------------------------------

	/**
	 * Should we use a Composer autoloader?
	 *
	 * CodeIgniter provides its own PSR4-compatible autoloader, but many
	 * third-party scripts will take advantage of the extra flexibility
	 * that Composer provides. This allows that support to be provided,
	 * and even with a customizable path to their autoloader.
	 */
	protected function loadComposerAutoloader()
	{
		$composer_autoload = $this->config->composerAutoload;

		if (empty($composer_autoload))
		{
			return;
		}

		if ($composer_autoload === true)
		{
			file_exists(APPPATH.'vendor/autoload.php')
				? require_once(APPPATH.'vendor/autoload.php')
				: log_message('error', '$this->config->\'composerAutoload\' is set to TRUE but '.APPPATH.
				'vendor/autoload.php was not found.');
		}
		elseif (file_exists($composer_autoload))
		{
			require_once($composer_autoload);
		}
		else
		{
			log_message('error',
				'Could not find the specified $this->config->\'composerAutoload\' path: '.$composer_autoload);
		}
	}
}
