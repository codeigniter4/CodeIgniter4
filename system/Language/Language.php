<?php namespace CodeIgniter\Language;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
class Language
{

	/**
	 * Stores the retrieved language lines
	 * from files for faster retrieval on
	 * second use.
	 *
	 * @var array
	 */
	protected $language = [];

	/**
	 * The current language/locale to work with.
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * Boolean value whether the intl
	 * libraries exist on the system.
	 *
	 * @var bool
	 */
	protected $intlSupport = false;

	/**
	 * Stores filenames that have been
	 * loaded so that we don't load them again.
	 *
	 * @var array
	 */
	protected $loadedFiles = [];

	//--------------------------------------------------------------------

	public function __construct(string $locale)
	{
		$this->locale = $locale;

		if (class_exists('\MessageFormatter'))
		{
			$this->intlSupport = true;
		};
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the current locale to use when performing string lookups.
	 *
	 * @param string $locale
	 *
	 * @return $this
	 */
	public function setLocale(string $locale = null)
	{
		if ( ! is_null($locale))
		{
			$this->locale = $locale;
		}

		return $this;
	}

	/**
	 * Parses the language string for a file, loads the file, if necessary,
	 * getting the line.
	 *
	 * @param string $line Line.
	 * @param array  $args Arguments.
	 *
	 * @return string|string[] Returns line.
	 */
	public function getLine(string $line, array $args = [])
	{
		// Parse out the file name and the actual alias.
		// Will load the language file and strings.
		list($file, $parsedLine) = $this->parseLine($line);

		$output = $this->language[$this->locale][$file][$parsedLine] ?? $line;

		if ( ! empty($args))
		{
			$output = $this->formatMessage($output, $args);
		}
		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the language string which should include the
	 * filename as the first segment (separated by period).
	 *
	 * @param string $line
	 *
	 * @return array
	 */
	protected function parseLine(string $line): array
	{
		// If there's no possibility of a filename being in the string
		// simply return the string, and they can parse the replacement
		// without it being in a file.
		if (strpos($line, '.') === false)
		{
			return [
				null,
				$line
			];
		}

		$file = substr($line, 0, strpos($line, '.'));
		$line = substr($line, strlen($file) + 1);

		if ( ! array_key_exists($line, $this->language))
		{
			$this->load($file, $this->locale);
		}

		return [
			$file,
			$this->language[$this->locale][$line] ?? $line
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Advanced message formatting.
	 *
	 * @param string|array $message Message.
	 * @param array	       $args    Arguments.
	 *
	 * @return string|array Returns formatted message.
	 */
	protected function formatMessage($message, array $args = [])
	{
		if ( ! $this->intlSupport || ! $args)
		{
			return $message;
		}

		if (is_array($message))
		{
			foreach ($message as $index => $value)
			{
				$message[$index] = $this->formatMessage($value, $args);
			}
			return $message;
		}

		return \MessageFormatter::formatMessage($this->locale, $message, $args);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads a language file in the current locale. If $return is true,
	 * will return the file's contents, otherwise will merge with
	 * the existing language lines.
	 *
	 * @param string $file
	 * @param string $locale
	 * @param bool   $return
	 *
	 * @return array|null
	 */
	protected function load(string $file, string $locale, bool $return = false)
	{
		if ( ! array_key_exists($locale, $this->loadedFiles))
		{
			$this->loadedFiles[$locale] = [];
		}

		if (in_array($file, $this->loadedFiles[$locale]))
		{
			// Don't load it more than once.
			return [];
		}

		if ( ! array_key_exists($locale, $this->language))
		{
			$this->language[$locale] = [];
		}

		if ( ! array_key_exists($file, $this->language[$locale]))
		{
			$this->language[$locale][$file] = [];
		}

		$path = "Language/{$locale}/{$file}.php";

		$lang = $this->requireFile($path);

		if ($return)
		{
			return $lang;
		}

		$this->loadedFiles[$locale][] = $file;

		// Merge our string
		$this->language[$this->locale][$file] = $lang;
	}

	//--------------------------------------------------------------------

	/**
	 * A simple method for including files that can be
	 * overridden during testing.
	 *
	 * @todo - should look into loading from other locations, also probably...
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	protected function requireFile(string $path): array
	{
		$files = service('locator')->search($path);

		foreach ($files as $file)
		{
			if ( ! is_file($file))
			{
				continue;
			}

			// On some OS's we were seeing failures
			// on this command returning boolean instead
			// of array during testing, so we've removed
			// the require_once for now.
			return require $file;
		}

		return [];
	}

	//--------------------------------------------------------------------
}
