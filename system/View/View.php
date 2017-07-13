<?php namespace CodeIgniter\View;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
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
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
use Config\Services;
use CodeIgniter\Log\Logger;

/**
 * Class View
 *
 * @package CodeIgniter\View
 */
class View implements RendererInterface
{

	/**
	 * Data that is made available to the Views.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * The base directory to look in for our Views.
	 *
	 * @var
	 */
	protected $viewPath;

	/**
	 * Instance of CodeIgniter\Loader for when
	 * we need to attempt to find a view
	 * that's not in standard place.
	 * @var
	 */
	protected $loader;

	/**
	 * Logger instance.
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Should we store performance info?
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * Cache stats about our performance here,
	 * when CI_DEBUG = true
	 * @var array
	 */
	protected $performanceData = [];

	/**
	 * @var \Config\View
	 */
	protected $config;

	/**
	 * Whether data should be saved between renders.
	 *
	 * @var bool
	 */
	protected $saveData;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param \Config\View  $config
	 * @param string        $viewPath
	 * @param type          $loader
	 * @param bool          $debug
	 * @param Logger        $logger
	 */
	public function __construct($config, string $viewPath = null, $loader = null, bool $debug = null, Logger $logger = null)
	{
		$this->config = $config;
		$this->viewPath = rtrim($viewPath, '/ ') . '/';
		$this->loader = is_null($loader) ? Services::locator() : $loader;
		$this->logger = is_null($logger) ? Services::logger() : $logger;
		$this->debug = is_null($debug) ? CI_DEBUG : $debug;
		$this->saveData = $config->saveData ?? null;
	}

	//--------------------------------------------------------------------

	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * Valid $options:
	 * 	- cache 		number of seconds to cache for
	 *  - cache_name	Name to use for cache
	 *
	 * @param string $view
	 * @param array  $options
	 * @param bool $saveData
	 *
	 * @return string
	 */
	public function render(string $view, array $options = null, $saveData = null): string
	{
		$start = microtime(true);

		// Store the results here so even if
		// multiple views are called in a view, it won't
		// clean it unless we mean it to.
		if ($saveData !== null)
		{
			$this->saveData = $saveData;
		}

		$view = str_replace('.php', '', $view) . '.php';

		// Was it cached?
		if (isset($options['cache']))
		{
			$cacheName = $options['cache_name'] ?? str_replace('.php', '', $view);

			if ($output = cache($cacheName))
			{
				$this->logPerformance($start, microtime(true), $view);
				return $output;
			}
		}

		$file = $this->viewPath . $view;

		if ( ! file_exists($file))
		{
			$file = $this->loader->locateFile($view, 'Views');
		}

		// locateFile will return an empty string if the file cannot be found.
		if (empty($file))
		{
			throw new \InvalidArgumentException('View file not found: ' . $view);
		}

		// Make our view data available to the view.
		extract($this->data);

		if ( ! $this->saveData)
		{
			$this->data = [];
		}

		ob_start();
		include($file); // PHP will be processed
		$output = ob_get_contents();
		@ob_end_clean();

		$this->logPerformance($start, microtime(true), $view);

		// Should we cache?
		if (isset($options['cache']))
		{
			cache()->save($cacheName, $output, (int) $options['cache']);
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Builds the output based upon a string and any
	 * data that has already been set.
	 * Cache does not apply, because there is no "key".
	 *
	 * @param string $view	The view contents
	 * @param array  $options  Reserved for 3rd-party uses since
	 *                         it might be needed to pass additional info
	 *                         to other template engines.
	 * @param bool   $saveData If true, will save data for use with any other calls,
	 *                         if false, will clean the data after displaying the view,
	 * 						   if not specified, use the config setting.
	 *
	 * @return string
	 */
	public function renderString(string $view, array $options = null, $saveData = null): string
	{
		$start = microtime(true);
		if (is_null($saveData))
		{
			$saveData = $this->config->saveData;
		}

		extract($this->data);

		if ( ! $saveData)
		{
			$this->data = [];
		}

		ob_start();
		$incoming = "?>" . $view;
		eval($incoming);
		$output = ob_get_contents();
		@ob_end_clean();

		$this->logPerformance($start, microtime(true), $this->excerpt($view));

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Extract first bit of a long string and add ellipsis
	 *
	 * @param string	$string
	 * @parm	int		$length
	 * @return string
	 */
	public function excerpt(string $string, int $length = 20): string
	{
		return (strlen($string) > $length) ? substr($string, 0, $length - 3) . '...' : $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 *
	 * @param array $data
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setData(array $data = [], string $context = null): RendererInterface
	{
		if ( ! empty($context))
		{
			$data = \esc($data, $context);
		}

		$this->data = array_merge($this->data, $data);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 *
	 * @param string $name
	 * @param mixed   $value
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setVar(string $name, $value = null, string $context = null): RendererInterface
	{
		if ( ! empty($context))
		{
			$value = \esc($value, $context);
		}

		$this->data[$name] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes all of the view data from the system.
	 *
	 * @return RendererInterface
	 */
	public function resetData()
	{
		$this->data = [];

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current data that will be displayed in the view.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the performance data that might have been collected
	 * during the execution. Used primarily in the Debug Toolbar.
	 *
	 * @return array
	 */
	public function getPerformanceData(): array
	{
		return $this->performanceData;
	}

	//--------------------------------------------------------------------

	/**
	 * Logs performance data for rendering a view.
	 *
	 * @param float  $start
	 * @param float  $end
	 * @param string $view
	 */
	protected function logPerformance(float $start, float $end, string $view)
	{
		if ( ! $this->debug)
			return;

		$this->performanceData[] = [
			'start'	 => $start,
			'end'	 => $end,
			'view'	 => $view
		];
	}

	//--------------------------------------------------------------------
}
