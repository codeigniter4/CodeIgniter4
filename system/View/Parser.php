<?php

namespace CodeIgniter\View;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
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

	/**
	 * Stores extracted noparse blocks.
	 *
	 * @var array
	 */
	protected $noparseBlocks = [];

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

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

		// Remove any possible PHP tags since we don't support it
		// and parseConditionals needs it clean anyway...
		$template = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $template);

		$template = $this->parseComments($template);
		$template = $this->extractNoparse($template);

		// Replace any conditional code here so we don't have to parse as much
		$template = $this->parseConditionals($template);

		// build the variable substitution list
		$replace = array();
		foreach ($data as $key => $val)
		{
			$replace = array_merge(
				$replace, is_array($val)
					? $this->parsePair($key, $val, $template)
					: $this->parseSingle($key, (string) $val, $template)
			);
		}

		unset($data);
		// do the substitutions
		foreach ($replace as $pattern => $content)
		{
			$template = preg_replace($pattern, $content, $template);
		}

		$template = $this->insertNoparse($template);

		return $template;
	}

	//--------------------------------------------------------------------

	protected function is_assoc($arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

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
		$val = esc($val, 'html');

		$pattern = '#'.$this->leftDelimiter.'\s*'.preg_quote($key).'\s*'.$this->rightDelimiter.'#ms';

		return [$pattern => (string) $val];
	}

	//--------------------------------------------------------------------

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
		// Holds the replacement patterns and contents
		// that will be used within a preg_replace in parse()
		$replace = [];

		// Find all matches of space-flexible versions of {tag}{/tag} so we
		// have something to loop over.
		preg_match_all(
				'#'.$this->leftDelimiter.'\s*'.preg_quote($variable).'\s*'.$this->rightDelimiter.'(.+?)' .
				$this->leftDelimiter.'\s*'.'/'.preg_quote($variable).'\s*'.$this->rightDelimiter.'#s',
				$template, $matches, PREG_SET_ORDER
		);

		/*
		 * Each match looks like:
		 *
		 * $match[0] {tag}...{/tag}
		 * $match[1] Contents inside the tag
		 */
		foreach ($matches as $match)
		{
			// Loop over each piece of $data, replacing
			// it's contents so that we know what to replace in parse()
			$str = '';  // holds the new contents for this tag pair.
			foreach ($data as $row)
			{
				$temp = [];
				$out  = $match[1];
				foreach ($row as $key => $val)
				{
					// For nested data, send us back through this method...
					if (is_array($val))
					{
						$pair = $this->parsePair($key, $val, $match[1]);
						if (!empty($pair))
						{
							$temp = array_merge($temp, $pair);
						}

						continue;
					}
					else if (is_object($val))
					{
						$val = 'Class: ' . get_class($val);
					}
					else if (is_resource($val))
					{
						$val = 'Resource';
					}

					$temp['#'.$this->leftDelimiter.'\s*'.preg_quote($key).'\s*'. $this->rightDelimiter.'#s'] = esc($val, 'html');
				}

				// Now replace our placeholders with the new content.
				foreach ($temp as $pattern => $content)
				{
					$out = preg_replace($pattern, $content, $out);
				}

				$str .= $out;
			}

			$replace['#'.$match[0].'#s'] = $str;
		}

		return $replace;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes any comments from the file. Comments are wrapped in {# #} symbols:
	 *
	 *      {# This is a comment #}
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	protected function parseComments(string $template): string
	{
		return preg_replace('/\{#.*?#\}/s', '', $template);
	}

	//--------------------------------------------------------------------

	/**
	 * Extracts noparse blocks, inserting a hash in its place so that
	 * those blocks of the page are not touched by parsing.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	protected function extractNoparse(string $template): string
	{
		$pattern = '/\{\s*noparse\s*\}(.*?)\{\s*\/noparse\s*\}/ms';

		/*
		 * $matches[][0] is the raw match
		 * $matches[][1] is the contents
		 */
		if (preg_match_all($pattern, $template, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				// Create a hash of the contents to insert in its place.
				$hash = md5($match[1]);
				$this->noparseBlocks[$hash] = $match[1];
				$template = str_replace($match[0], "noparse_{$hash}", $template);
			}
		}

		return $template;
	}

	//--------------------------------------------------------------------

	/**
	 * Re-inserts the noparsed contents back into the template.
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function insertNoparse(string $template): string
	{
		foreach ($this->noparseBlocks as $hash => $replace)
		{
			$template = str_replace("noparse_{$hash}", $replace, $template);
			unset($this->noparseBlocks[$hash]);
		}

		return $template;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses any conditionals in the code, removing blocks that don't
	 * pass so we don't try to parse it later.
	 *
	 * Valid conditionals:
	 *  - if
	 *  - elseif
	 *  - else
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	protected function parseConditionals(string $template): string
	{
		$pattern = '/\{\s*(if|elseif)\s*((?:\()?(.*?)(?:\))?)\s*\}/ms';

		/**
		 * For each match:
		 * [0] = raw match `{if var}`
		 * [1] = conditional `if`
		 * [2] = condition `do === true`
		 * [3] = same as [2]
		 */
		preg_match_all($pattern, $template, $matches, PREG_SET_ORDER);

		foreach ($matches as $match)
		{
			// Build the string to replace the `if` statement with.
			$condition = $match[2];

			$statement = $match[1] == 'elseif'
				? '<?php elseif ($'.$condition.'): ?>'
				: '<?php if ($'.$condition.'): ?>';
			$template = str_replace($match[0], $statement, $template);
		}

		$template = preg_replace('/\{\s*else\s*\}/ms', '<?php else: ?>', $template);
		$template = preg_replace('/\{\s*endif\s*\}/ms', '<?php endif; ?>', $template);

		// Parse the PHP itself, or insert an error so they can debug
		ob_start();
		extract($this->data);
		$result = eval('?>'.$template.'<?php ');

		if ($result === false)
		{
			$output = 'You have a syntax error in your Parser tags: ';
			throw new \RuntimeException($output.str_replace(['?>', '<?php '], '', $template));
		}

		return ob_get_clean();
	}

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------
}
