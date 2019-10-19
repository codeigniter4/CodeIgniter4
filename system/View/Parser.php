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

use CodeIgniter\Log\Logger;
use CodeIgniter\View\Exceptions\ViewException;

/**
 * Class Parser
 *
 *  ClassFormerlyKnownAsTemplateParser
 *
 * @package CodeIgniter\View
 */
class Parser extends View
{

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

	/**
	 * Stores any plugins registered at run-time.
	 *
	 * @var array
	 */
	protected $plugins = [];

	/**
	 * Stores the context for each data element
	 * when set by `setData` so the context is respected.
	 *
	 * @var array
	 */
	protected $dataContexts = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param \Config\View $config
	 * @param string       $viewPath
	 * @param mixed        $loader
	 * @param boolean      $debug
	 * @param Logger       $logger
	 */
	public function __construct($config, string $viewPath = null, $loader = null, bool $debug = null, Logger $logger = null)
	{
		// Ensure user plugins override core plugins.
		$this->plugins = $config->plugins ?? [];

		parent::__construct($config, $viewPath, $loader, $debug, $logger);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view,
	 * replacing them with any data that has already been set.
	 *
	 * @param string  $view
	 * @param array   $options
	 * @param boolean $saveData
	 *
	 * @return string
	 */
	public function render(string $view, array $options = null, bool $saveData = null): string
	{
		$start = microtime(true);
		if (is_null($saveData))
		{
			$saveData = $this->config->saveData;
		}

		$fileExt = pathinfo($view, PATHINFO_EXTENSION);
		$view    = empty($fileExt) ? $view . '.php' : $view; // allow Views as .html, .tpl, etc (from CI3)

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

		if (! is_file($file))
		{
			$file = $this->loader->locateFile($view, 'Views');
		}

		// locateFile will return an empty string if the file cannot be found.
		if (empty($file))
		{
			throw ViewException::forInvalidFile($file);
		}

		$template = file_get_contents($file);
		$output   = $this->parse($template, $this->data, $options);
		$this->logPerformance($start, microtime(true), $view);

		if (! $saveData)
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
	 * @param string  $template
	 * @param array   $options
	 * @param boolean $saveData
	 *
	 * @return string
	 */
	public function renderString(string $template, array $options = null, bool $saveData = null): string
	{
		$start = microtime(true);
		if (is_null($saveData))
		{
			$saveData = $this->config->saveData;
		}

		$output = $this->parse($template, $this->data, $options);

		$this->logPerformance($start, microtime(true), $this->excerpt($template));

		if (! $saveData)
		{
			$this->data = [];
		}
		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 * In the Parser, we need to store the context here
	 * so that the variable is correctly handled within the
	 * parsing itself, and contexts (including raw) are respected.
	 *
	 * @param array  $data
	 * @param string $context The context to escape it for: html, css, js, url, raw
	 *                        If 'raw', no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setData(array $data = [], string $context = null): RendererInterface
	{
		if (! empty($context))
		{
			foreach ($data as $key => &$value)
			{
				if (is_array($value))
				{
					foreach ($value as &$obj)
					{
						$obj = $this->objectToArray($obj);
					}
				}
				else
				{
					$value = $this->objectToArray($value);
				}

				$this->dataContexts[$key] = $context;
			}
		}

		$this->data = array_merge($this->data, $data);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * @param  string $template
	 * @param  array  $data
	 * @param  array  $options  Future options
	 * @return string
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

		// Handle any plugins before normal data, so that
		// it can potentially modify any template between its tags.
		$template = $this->parsePlugins($template);

		// loop over the data variables, replacing
		// the content as we go.
		foreach ($data as $key => $val)
		{
			$escape = true;

			if (is_array($val))
			{
				$escape  = false;
				$replace = $this->parsePair($key, $val, $template);
			}
			else
			{
				$replace = $this->parseSingle($key, (string) $val);
			}

			foreach ($replace as $pattern => $content)
			{
				$template = $this->replaceSingle($pattern, $content, $template, $escape);
			}
		}

		$template = $this->insertNoparse($template);

		return $template;
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a single key/value, extracting it
	 *
	 * @param  string $key
	 * @param  string $val
	 * @return array
	 */
	protected function parseSingle(string $key, string $val): array
	{
		$pattern = '#' . $this->leftDelimiter . '!?\s*' . preg_quote($key) . '\s*\|*\s*([|a-zA-Z0-9<>=\(\),:_\-\s\+]+)*\s*!?' . $this->rightDelimiter . '#ms';

		return [$pattern => $val];
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a tag pair
	 *
	 * Parses tag pairs: {some_tag} string... {/some_tag}
	 *
	 * @param  string $variable
	 * @param  array  $data
	 * @param  string $template
	 * @return array
	 */
	protected function parsePair(string $variable, array $data, string $template): array
	{
		// Holds the replacement patterns and contents
		// that will be used within a preg_replace in parse()
		$replace = [];

		// Find all matches of space-flexible versions of {tag}{/tag} so we
		// have something to loop over.
		preg_match_all(
				'#' . $this->leftDelimiter . '\s*' . preg_quote($variable) . '\s*' . $this->rightDelimiter . '(.+?)' .
				$this->leftDelimiter . '\s*' . '/' . preg_quote($variable) . '\s*' . $this->rightDelimiter . '#s', $template, $matches, PREG_SET_ORDER
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
				// Objects that have a `toArray()` method should be
				// converted with that method (i.e. Entities)
				if (is_object($row) && method_exists($row, 'toArray'))
				{
					$row = $row->toArray();
				}
				// Otherwise, cast as an array and it will grab public properties.
				else if (is_object($row))
				{
					$row = (array)$row;
				}

				$temp  = [];
				$pairs = [];
				$out   = $match[1];
				foreach ($row as $key => $val)
				{
					// For nested data, send us back through this method...
					if (is_array($val))
					{
						$pair = $this->parsePair($key, $val, $match[1]);

						if (! empty($pair))
						{
							$pairs[array_keys( $pair )[0]] = true;
							$temp                          = array_merge($temp, $pair);
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

					$temp['#' . $this->leftDelimiter . '!?\s*' . preg_quote($key) . '\s*\|*\s*([|\w<>=\(\),:_\-\s\+]+)*\s*!?' . $this->rightDelimiter . '#s'] = $val;
				}

				// Now replace our placeholders with the new content.
				foreach ($temp as $pattern => $content)
				{
					$out = $this->replaceSingle($pattern, $content, $out, ! isset( $pairs[$pattern] ) );
				}

				$str .= $out;
			}

			$replace['#' . $match[0] . '#s'] = $str;
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
				$hash                       = md5($match[1]);
				$this->noparseBlocks[$hash] = $match[1];
				$template                   = str_replace($match[0], "noparse_{$hash}", $template);
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

			$statement = $match[1] === 'elseif' ? '<?php elseif (' . $condition . '): ?>' : '<?php if (' . $condition . '): ?>';
			$template  = str_replace($match[0], $statement, $template);
		}

		$template = preg_replace('/\{\s*else\s*\}/ms', '<?php else: ?>', $template);
		$template = preg_replace('/\{\s*endif\s*\}/ms', '<?php endif; ?>', $template);

		// Parse the PHP itself, or insert an error so they can debug
		ob_start();
		extract($this->data);
		try
		{
			eval('?>' . $template . '<?php ');
		}
		catch (\ParseError $e)
		{
			ob_end_clean();
			throw ViewException::forTagSyntaxError(str_replace(['?>', '<?php '], '', $template));
		}
		return ob_get_clean();
	}

	//--------------------------------------------------------------------

	/**
	 * Over-ride the substitution field delimiters.
	 *
	 * @param  string $leftDelimiter
	 * @param  string $rightDelimiter
	 * @return RendererInterface
	 */
	public function setDelimiters($leftDelimiter = '{', $rightDelimiter = '}'): RendererInterface
	{
		$this->leftDelimiter  = $leftDelimiter;
		$this->rightDelimiter = $rightDelimiter;
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles replacing a pseudo-variable with the actual content. Will double-check
	 * for escaping brackets.
	 *
	 * @param $pattern
	 * @param $content
	 * @param $template
	 * @param boolean  $escape
	 *
	 * @return string
	 */
	protected function replaceSingle($pattern, $content, $template, bool $escape = false): string
	{
		// Any dollar signs in the pattern will be mis-interpreted, so slash them
		$pattern = addcslashes($pattern, '$');

		// Replace the content in the template
		$template = preg_replace_callback($pattern, function ($matches) use ($content, $escape) {
			// Check for {! !} syntax to not-escape this one.
			if (strpos($matches[0], '{!') === 0 && substr($matches[0], -2) === '!}')
			{
				$escape = false;
			}

			return $this->prepareReplacement($matches, $content, $escape);
		}, $template);

		return $template;
	}

	//--------------------------------------------------------------------

	/**
	 * Callback used during parse() to apply any filters to the value.
	 *
	 * @param array   $matches
	 * @param string  $replace
	 * @param boolean $escape
	 *
	 * @return string
	 */
	protected function prepareReplacement(array $matches, string $replace, bool $escape = true): string
	{
		$orig = array_shift($matches);

		// Our regex earlier will leave all chained values on a single line
		// so we need to break them apart so we can apply them all.
		$filters = isset($matches[0]) ? explode('|', $matches[0]) : [];

		if ($escape && ! isset($matches[0]))
		{
			if ($context = $this->shouldAddEscaping($orig))
			{
				$filters[] = "esc({$context})";
			}
		}

		$replace = $this->applyFilters($replace, $filters);

		return $replace;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks the placeholder the view provided to see if we need to provide any autoescaping.
	 *
	 * @param string $key
	 *
	 * @return false|string
	 */
	public function shouldAddEscaping(string $key)
	{
		$escape = false;

		$key = trim(str_replace(['{', '}'], '', $key));

		// If the key has a context stored (from setData)
		// we need to respect that.
		if (array_key_exists($key, $this->dataContexts))
		{
			if ($this->dataContexts[$key] !== 'raw')
			{
				return $this->dataContexts[$key];
			}
		}
		// No pipes, then we know we need to escape
		elseif (strpos($key, '|') === false)
		{
			$escape = 'html';
		}
		// If there's a `noescape` then we're definitely false.
		elseif (strpos($key, 'noescape') !== false)
		{
			$escape = false;
		}
		// If no `esc` filter is found, then we'll need to add one.
		elseif (! preg_match('/\s+esc/', $key))
		{
			$escape = 'html';
		}

		return $escape;
	}

	//--------------------------------------------------------------------

	/**
	 * Given a set of filters, will apply each of the filters in turn
	 * to $replace, and return the modified string.
	 *
	 * @param string $replace
	 * @param array  $filters
	 *
	 * @return string
	 */
	protected function applyFilters(string $replace, array $filters): string
	{
		// Determine the requested filters
		foreach ($filters as $filter)
		{
			// Grab any parameter we might need to send
			preg_match('/\([a-zA-Z0-9\-:_ +,<>=]+\)/', $filter, $param);

			// Remove the () and spaces to we have just the parameter left
			$param = ! empty($param) ? trim($param[0], '() ') : null;

			// Params can be separated by commas to allow multiple parameters for the filter
			if (! empty($param))
			{
				$param = explode(',', $param);

				// Clean it up
				foreach ($param as &$p)
				{
					$p = trim($p, ' "');
				}
			}
			else
			{
				$param = [];
			}

			// Get our filter name
			$filter = ! empty($param) ? trim(strtolower(substr($filter, 0, strpos($filter, '(')))) : trim($filter);

			if (! array_key_exists($filter, $this->config->filters))
			{
				continue;
			}

			// Filter it....
			$replace = $this->config->filters[$filter]($replace, ...$param);
		}

		return $replace;
	}

	//--------------------------------------------------------------------
	// Plugins
	//--------------------------------------------------------------------

	/**
	 * Scans the template for any parser plugins, and attempts to execute them.
	 * Plugins are notated based on {+ +} opening and closing braces.
	 *
	 * When encountered,
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	protected function parsePlugins(string $template)
	{
		foreach ($this->plugins as $plugin => $callable)
		{
			// Paired tags are enclosed in an array in the config array.
			$isPair   = is_array($callable);
			$callable = $isPair ? array_shift($callable) : $callable;

			$pattern = $isPair ? '#{\+\s*' . $plugin . '([\w\d=-_:\+\s()/\"@.]*)?\s*\+}(.+?){\+\s*/' . $plugin . '\s*\+}#ims' : '#{\+\s*' . $plugin . '([\w\d=-_:\+\s()/\"@.]*)?\s*\+}#ims';

			/**
			 * Match tag pairs
			 *
			 * Each match is an array:
			 *   $matches[0] = entire matched string
			 *   $matches[1] = all parameters string in opening tag
			 *   $matches[2] = content between the tags to send to the plugin.
			 */
			preg_match_all($pattern, $template, $matches, PREG_SET_ORDER);

			if (empty($matches))
			{
				continue;
			}

			foreach ($matches as $match)
			{
				$params = [];

				// Split on "words", but keep quoted groups together, accounting for escaped quotes.
				// Note: requires double quotes, not single quotes.
				$parts = str_getcsv($match[1], ' ');

				foreach ($parts as $part)
				{
					if (empty($part))
					{
						continue;
					}

					if (strpos($part, '=') !== false)
					{
						list($a, $b) = explode('=', $part);
						$params[$a]  = $b;
					}
					else
					{
						$params[] = $part;
					}
				}
				unset($parts);

				$template = $isPair ? str_replace($match[0], $callable($match[2], $params), $template) : str_replace($match[0], $callable($params), $template);
			}
		}

		return $template;
	}

	/**
	 * Makes a new plugin available during the parsing of the template.
	 *
	 * @param string   $alias
	 * @param callable $callback
	 *
	 * @param boolean  $isPair
	 *
	 * @return $this
	 */
	public function addPlugin(string $alias, callable $callback, bool $isPair = false)
	{
		$this->plugins[$alias] = $isPair ? [$callback] : $callback;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a plugin from the available plugins.
	 *
	 * @param string $alias
	 *
	 * @return $this
	 */
	public function removePlugin(string $alias)
	{
		unset($this->plugins[$alias]);

		return $this;
	}

	/**
	 * Converts an object to an array, respecting any
	 * toArray() methods on an object.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function objectToArray($value)
	{
		// Objects that have a `toArray()` method should be
		// converted with that method (i.e. Entities)
		if (is_object($value) && method_exists($value, 'toArray'))
		{
			$value = $value->toArray();
		}
		// Otherwise, cast as an array and it will grab public properties.
		else if (is_object($value))
		{
			$value = (array)$value;
		}

		return $value;
	}

	//--------------------------------------------------------------------
}
