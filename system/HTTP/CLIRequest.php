<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP;

use Config\App;

/**
 * Class CLIRequest
 *
 * Represents a request from the command-line. Provides additional
 * tools to interact with that request since CLI requests are not
 * static like HTTP requests might be.
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under.
 *
 * http://fuelphp.com
 *
 * @package CodeIgniter\HTTP
 */
class CLIRequest extends Request
{

	/**
	 * Stores the segments of our cli "URI" command.
	 *
	 * @var array
	 */
	protected $segments = [];

	/**
	 * Command line options and their values.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Set the expected HTTP verb
	 *
	 * @var string
	 */
	protected $method = 'cli';

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param App $config
	 */
	public function __construct(App $config)
	{
		parent::__construct($config);

		// Don't terminate the script when the cli's tty goes away
		ignore_user_abort(true);

		$this->parseCommand();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the "path" of the request script so that it can be used
	 * in routing to the appropriate controller/method.
	 *
	 * The path is determined by treating the command line arguments
	 * as if it were a URL - up until we hit our first option.
	 *
	 * Example:
	 *      php index.php users 21 profile -foo bar
	 *
	 *      // Routes to /users/21/profile (index is removed for routing sake)
	 *      // with the option foo = bar.
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		$path = implode('/', $this->segments);

		return empty($path) ? '' : $path;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an associative array of all CLI options found, with
	 * their values.
	 *
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the path segments.
	 *
	 * @return array
	 */
	public function getSegments(): array
	{
		return $this->segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the value for a single CLI option that was passed in.
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function getOption(string $key)
	{
		return $this->options[$key] ?? null;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the options as a string, suitable for passing along on
	 * the CLI to other commands.
	 *
	 * Example:
	 *      $options = [
	 *          'foo' => 'bar',
	 *          'baz' => 'queue some stuff'
	 *      ];
	 *
	 *      getOptionString() = '-foo bar -baz "queue some stuff"'
	 *
	 * @return string
	 */
	public function getOptionString(): string
	{
		if (empty($this->options))
		{
			return '';
		}

		$out = '';

		foreach ($this->options as $name => $value)
		{
			// If there's a space, we need to group
			// so it will pass correctly.
			if (strpos($value, ' ') !== false)
			{
				$value = '"' . $value . '"';
			}

			$out .= "-{$name} $value ";
		}

		return trim($out);
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the command line it was called from and collects all options
	 * and valid segments.
	 *
	 * NOTE: I tried to use getopt but had it fail occasionally to find
	 * any options, where argv has always had our back.
	 */
	protected function parseCommand()
	{
		$args = $this->getServer('argv');
		array_shift($args); // Scrap index.php

		$optionValue = false;

		foreach ($args as $i => $arg)
		{
			if (mb_strpos($arg, '-') !== 0)
			{
				if ($optionValue)
				{
					$optionValue = false;
				}
				else
				{
					$this->segments[] = filter_var($arg, FILTER_SANITIZE_STRING);
				}

				continue;
			}

			$arg   = filter_var(ltrim($arg, '-'), FILTER_SANITIZE_STRING);
			$value = null;

			if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0)
			{
				$value       = filter_var($args[$i + 1], FILTER_SANITIZE_STRING);
				$optionValue = true;
			}

			$this->options[$arg] = $value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if this request was made from the command line (CLI).
	 *
	 * @return boolean
	 */
	public function isCLI(): bool
	{
		return is_cli();
	}

	//--------------------------------------------------------------------
}
