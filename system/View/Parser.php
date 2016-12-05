<?php

namespace CodeIgniter\View;

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
 * @since	Version 3.0.0
 * @filesource
 */
use Config\Services;
use CodeIgniter\Log\Logger;

/**
 * Class Parser
 *
 *  ClassFormerlyKnownAsTemplateParser 
 * 
 * @todo Views\Parser_Test
 * @tofo Common::parse
 * @todo user guide
 * @todo options -> delimiters
 *
 * @package CodeIgniter\View
 */
class Parser extends View {

	/**
	 * Left delimiter character for pseudo vars
	 *
	 * @var string
	 */
	public $leftDelimiter = '{';

	/**
	 * Right delimiter character for pseudo vars
	 *
	 * @var string
	 */
	public $rightDelimiter = '}';

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param \Config\View  $config
	 * @param string $viewPath
	 * @param type $loader
	 * @param bool $debug
	 * @param Logger $logger
	 */
	public function __construct($config, string $viewPath = null, $loader = null, bool $debug = null, Logger $logger = null)
	{
		parent::__construct($config, $viewPath, $loader, $debug, $logger);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view,
	 * replacing them with any data that has already been set.
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
        if (is_null($saveData))
        {
            $saveData = $this->config->saveData;
        }

		$view = str_replace('.php', '', $view) . '.php';

		// Was it cached?
		if (isset($options['cache']))
		{
			$cacheName = $options['cache_name'] ?: str_replace('.php', '', $view);

			if ($output = cache($cacheName))
			{
				$this->logPerformance($start, microtime(true), $view);
				return $output;
			}
		}

		$file = $this->viewPath . $view;

		if (!file_exists($file))
		{
			$file = $this->loader->locateFile($view, 'Views');
		}

		// locateFile will return an empty string if the file cannot be found.
		if (empty($file))
		{
			throw new \InvalidArgumentException('View file not found: ' . $file);
		}

		$template = file_get_contents($file);
		$output = $this->parse($template, $this->data, $options);
		$this->logPerformance($start, microtime(true), $view);

		if (!$saveData)
		{
			$this->data = [];
		}
		// Should we cache?
		if (isset($options['cache']))
		{
			cache()->save($cacheName, $output, (int) $options['cache']);
		}

		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a String
	 *
	 * Parses pseudo-variables contained in the specified string,
	 * replacing them with any data that has already been set.
	 *
	 * @param string $template
	 * @param array  $options  
	 * @param bool $saveData
	 *
	 * @return	string
	 */
	public function renderString(string $template, array $options = null, $saveData = null): string
	{
		$start = microtime(true);
        if (is_null($saveData))
        {
            $saveData = $this->config->saveData;
        }

		$output = $this->parse($template, $this->data, $options);

		$this->logPerformance($start, microtime(true), $this->excerpt($template));

		if (!$saveData)
		{
			$this->data = [];
		}
		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * @param string $template
	 * @param array  $data
	 * @param array $options	Future options
	 * @return	string
	 */
	protected function parse(string $template, array $data = [], array $options = null): string
	{
		if ($template === '')
		{
			return '';
		}

		// build the variable substitution list
		$replace = array();
		foreach ($data as $key => $val)
		{
			$replace = array_merge(
					$replace, is_array($val) ? $this->parsePair($key, $val, $template) : $this->parseSingle($key, (string) $val, $template)
			);
		}

		unset($data);
		// do the substitutions
		$template = strtr($template, $replace);

		return $template;
	}

	// --------------------------------------------------------------------

	protected function is_assoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	function strpos_all($haystack, $needle)
	{
		$offset = 0;
		$allpos = array();
		while (($pos = strpos($haystack, $needle, $offset)) !== FALSE)
		{
			$offset = $pos + 1;
			$allpos[] = $pos;
		}
		return $allpos;
	}

// --------------------------------------------------------------------

	/**
	 * Parse a single key/value
	 *
	 * @param	string $key
	 * @param	string $val
	 * @param	string $template
	 * @return	array
	 */
	protected function parseSingle(string $key, string $val, string $template): array
	{
		return array($this->leftDelimiter . $key . $this->rightDelimiter => (string) $val);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a tag pair
	 *
	 * Parses tag pairs: {some_tag} string... {/some_tag}
	 *
	 * @param	string $variable
	 * @param	array	$data
	 * @param	string	$template
	 * @return	array
	 */
	protected function parsePair(string $variable, array $data, string $template): array
	{
		$replace = array();
		preg_match_all(
				'#' . preg_quote($this->leftDelimiter . $variable . $this->rightDelimiter) . '(.+?)' . 
				preg_quote($this->leftDelimiter . '/' . $variable . $this->rightDelimiter) . '#s', 
				$template, $matches, PREG_SET_ORDER
		);

		foreach ($matches as $match)
		{
			$str = '';
			foreach ($data as $row)
			{
				$temp = array();
				foreach ($row as $key => $val)
				{
					if (is_array($val))
					{
						$pair = $this->parsePair($key, $val, $match[1]);
						if (!empty($pair))
						{
							$temp = array_merge($temp, $pair);
						}

						continue;
					}

					$temp[$this->leftDelimiter . $key . $this->rightDelimiter] = $val;
				}

				$str .= strtr($match[1], $temp);
			}

			$replace[$match[0]] = $str;
		}

		return $replace;
	}

	/**
	 * Over-ride the substitution field delimiters.
	 *
	 * @param	string $leftDelimiter
	 * @param	string $rightDelimiter
	 * @return	RendererInterface
	 */
	public function setDelimiters($leftDelimiter = '{', $rightDelimiter = '}'): RendererInterface
	{
		$this->leftDelimiter = $leftDelimiter;
		$this->rightDelimiter = $rightDelimiter;
		return $this;
	}

}
