<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\View;

use CodeIgniter\View\Exceptions\ViewException;
use Config\Services;
use Psr\Log\LoggerInterface;

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
	 * @var string
	 */
	protected $viewPath;

	/**
	 * The render variables
	 *
	 * @var array
	 */
	protected $renderVars = [];

	/**
	 * Instance of FileLocator for when
	 * we need to attempt to find a view
	 * that's not in standard place.
	 *
	 * @var \CodeIgniter\Autoloader\FileLocator
	 */
	protected $loader;

	/**
	 * Logger instance.
	 *
	 * @var \CodeIgniter\Log\Logger
	 */
	protected $logger;

	/**
	 * Should we store performance info?
	 *
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Cache stats about our performance here,
	 * when CI_DEBUG = true
	 *
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
	 * @var boolean
	 */
	protected $saveData;

	/**
	 * Number of loaded views
	 *
	 * @var integer
	 */
	protected $viewsCount = 0;

	/**
	 * The name of the layout being used, if any.
	 * Set by the `extend` method used within views.
	 *
	 * @var string
	 */
	protected $layout;

	/**
	 * Holds the sections and their data.
	 *
	 * @var array
	 */
	protected $sections = [];

	/**
	 * The name of the current section being rendered,
	 * if any.
	 *
	 * @var string
	 */
	protected $currentSection;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param \Config\View    $config
	 * @param string          $viewPath
	 * @param mixed           $loader
	 * @param boolean         $debug
	 * @param LoggerInterface $logger
	 */
	public function __construct($config, string $viewPath = null, $loader = null, bool $debug = null, LoggerInterface $logger = null)
	{
		$this->config   = $config;
		$this->viewPath = rtrim($viewPath, '/ ') . '/';
		$this->loader   = is_null($loader) ? Services::locator() : $loader;
		$this->logger   = is_null($logger) ? Services::logger() : $logger;
		$this->debug    = is_null($debug) ? CI_DEBUG : $debug;
		$this->saveData = $config->saveData ?? null;
	}

	//--------------------------------------------------------------------

	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 *
	 * Valid $options:
	 *     - cache 		number of seconds to cache for
	 *  - cache_name	Name to use for cache
	 *
	 * @param string  $view
	 * @param array   $options
	 * @param boolean $saveData
	 *
	 * @return string
	 */
	public function render(string $view, array $options = null, bool $saveData = null): string
	{
		$this->renderVars['start'] = microtime(true);

		// Store the results here so even if
		// multiple views are called in a view, it won't
		// clean it unless we mean it to.
		if ($saveData !== null)
		{
			$this->saveData = $saveData;
		}

		$fileExt                     = pathinfo($view, PATHINFO_EXTENSION);
		$realPath                    = empty($fileExt) ? $view . '.php' : $view; // allow Views as .html, .tpl, etc (from CI3)
		$this->renderVars['view']    = $realPath;
		$this->renderVars['options'] = $options;

		// Was it cached?
		if (isset($this->renderVars['options']['cache']))
		{
			$this->renderVars['cacheName'] = $this->renderVars['options']['cache_name'] ?? str_replace('.php', '', $this->renderVars['view']);

			if ($output = cache($this->renderVars['cacheName']))
			{
				$this->logPerformance($this->renderVars['start'], microtime(true), $this->renderVars['view']);
				return $output;
			}
		}

		$this->renderVars['file'] = $this->viewPath . $this->renderVars['view'];

		if (! is_file($this->renderVars['file']))
		{
			$this->renderVars['file'] = $this->loader->locateFile($this->renderVars['view'], 'Views', empty($fileExt) ? 'php' : $fileExt);
		}

		// locateFile will return an empty string if the file cannot be found.
		if (empty($this->renderVars['file']))
		{
			throw ViewException::forInvalidFile($this->renderVars['view']);
		}

		// Make our view data available to the view.
		extract($this->data);

		if (! $this->saveData)
		{
			$this->data = [];
		}

		ob_start();
		include($this->renderVars['file']); // PHP will be processed
		$output = ob_get_contents();
		@ob_end_clean();

		// When using layouts, the data has already been stored
		// in $this->sections, and no other valid output
		// is allowed in $output so we'll overwrite it.
		if (! is_null($this->layout) && empty($this->currentSection))
		{
			$layoutView   = $this->layout;
			$this->layout = null;
			$output       = $this->render($layoutView, $options, $saveData);
		}

		$this->logPerformance($this->renderVars['start'], microtime(true), $this->renderVars['view']);

		if ($this->debug && (! isset($options['debug']) || $options['debug'] === true))
		{
			$toolbarCollectors = config(\Config\Toolbar::class)->collectors;

			if (in_array(\CodeIgniter\Debug\Toolbar\Collectors\Views::class, $toolbarCollectors))
			{
				// Clean up our path names to make them a little cleaner
				foreach (['APPPATH', 'SYSTEMPATH', 'ROOTPATH'] as $path)
				{
					if (strpos($this->renderVars['file'], constant($path)) === 0)
					{
						$this->renderVars['file'] = str_replace(constant($path), $path . '/', $this->renderVars['file']);
						break;
					}
				}
				$this->renderVars['file'] = ++$this->viewsCount . ' ' . $this->renderVars['file'];
				$output                   = '<!-- DEBUG-VIEW START ' . $this->renderVars['file'] . ' -->' . PHP_EOL
					. $output . PHP_EOL
					. '<!-- DEBUG-VIEW ENDED ' . $this->renderVars['file'] . ' -->' . PHP_EOL;
			}
		}

		// Should we cache?
		if (isset($this->renderVars['options']['cache']))
		{
			cache()->save($this->renderVars['cacheName'], $output, (int) $this->renderVars['options']['cache']);
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Builds the output based upon a string and any
	 * data that has already been set.
	 * Cache does not apply, because there is no "key".
	 *
	 * @param string  $view     The view contents
	 * @param array   $options  Reserved for 3rd-party uses since
	 *                          it might be needed to pass additional info
	 *                          to other template engines.
	 * @param boolean $saveData If true, will save data for use with any other calls,
	 *                          if false, will clean the data after displaying the view,
	 *                             if not specified, use the config setting.
	 *
	 * @return string
	 */
	public function renderString(string $view, array $options = null, bool $saveData = null): string
	{
		$start = microtime(true);
		if (is_null($saveData))
		{
			$saveData = $this->config->saveData;
		}

		extract($this->data);

		if (! $saveData)
		{
			$this->data = [];
		}

		ob_start();
		$incoming = '?>' . $view;
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
	 * @param  string  $string
	 * @param  integer $length
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
	 * @param array  $data
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setData(array $data = [], string $context = null): RendererInterface
	{
		if (! empty($context))
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
	 * @param mixed  $value
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If null, no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setVar(string $name, $value = null, string $context = null): RendererInterface
	{
		if (! empty($context))
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
	public function resetData(): RendererInterface
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
	public function getData(): array
	{
		return $this->data;
	}

	//--------------------------------------------------------------------

	/**
	 * Specifies that the current view should extend an existing layout.
	 *
	 * @param string $layout
	 *
	 * @return void
	 */
	public function extend(string $layout)
	{
		$this->layout = $layout;
	}

	//--------------------------------------------------------------------

	/**
	 * Starts holds content for a section within the layout.
	 *
	 * @param string $name
	 */
	public function section(string $name)
	{
		$this->currentSection = $name;

		ob_start();
	}

	//--------------------------------------------------------------------

	/**
	 *
	 *
	 * @throws \Zend\Escaper\Exception\RuntimeException
	 */
	public function endSection()
	{
		$contents = ob_get_clean();

		if (empty($this->currentSection))
		{
			throw new \RuntimeException('View themes, no current section.');
		}

		// Ensure an array exists so we can store multiple entries for this.
		if (! array_key_exists($this->currentSection, $this->sections))
		{
			$this->sections[$this->currentSection] = [];
		}
		$this->sections[$this->currentSection][] = $contents;

		$this->currentSection = null;
	}

	//--------------------------------------------------------------------

	/**
	 * Renders a section's contents.
	 *
	 * @param string $sectionName
	 */
	public function renderSection(string $sectionName)
	{
		if (! isset($this->sections[$sectionName]))
		{
			echo '';

			return;
		}

		foreach ($this->sections[$sectionName] as $contents)
		{
			echo $contents;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Used within layout views to include additional views.
	 *
	 * @param string     $view
	 * @param array|null $options
	 * @param null       $saveData
	 *
	 * @return string
	 */
	public function include(string $view, array $options = null, $saveData = null): string
	{
		return $this->render($view, $options, $saveData);
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
		if (! $this->debug)
		{
			return;
		}

		$this->performanceData[] = [
			'start' => $start,
			'end'   => $end,
			'view'  => $view,
		];
	}

	//--------------------------------------------------------------------
}
