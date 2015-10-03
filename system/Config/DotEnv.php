<?php namespace CodeIgniter\Config;

class DotEnv
{
	/**
	 * The directory where the .env file can be located.
	 *
	 * @var string
	 */
	protected $path;

	//--------------------------------------------------------------------

	/**
	 * Builds the path to our file.
	 *
	 * @param string $path
	 * @param string $file
	 */
	public function __construct(string $path, $file = '.env')
	{
		if ( ! is_string($file))
		{
			$file = '.env';
		}

		$this->path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
	}

	//--------------------------------------------------------------------

	/**
	 * The main entry point, will load the .env file and process it
	 * so that we end up with all settings in the PHP environment vars
	 * (i.e. getenv(), $_ENV, and $_SERVER)
	 */
	public function load()
	{
		// We don't want to enforce the presence of a .env file,
		// they should be optional.
		if ( ! is_file($this->path))
		{
			return;
		}

		// Ensure file is readable
		if ( ! is_readable($this->path))
		{
			throw new \InvalidArgumentException("The .env file is not readable: {$this->path}");
		}

		$lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($lines as $line)
		{
			// Is it a comment?
			if (strpos(trim($line), '#') === 0)
			{
				continue;
			}

			// If there is an equal sign, then we know we
			// are assigning a variable.
			if (strpos($line, '=') !== false)
			{
				$this->setVariable($line);
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the variable into the environment. Will parse the string
	 * first to look for {name}={value} pattern, ensure that nested
	 * variables are handled, and strip it of single and double quotes.
	 *
	 * @param string $name
	 * @param string $value
	 */
	protected function setVariable(string $name, string $value = '')
	{
		list($name, $value) = $this->normaliseVariable($name, $value);

		// Don't overwrite existing environment variables.
		if ( ! is_null($this->getVariable($name)))
		{
			return;
		}

		putenv("$name=$value");
		$_ENV[$name] = $value;
		$_SERVER[$name] = $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses for assignment, cleans the $name and $value, and ensures
	 * that nested variables are handled.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return array
	 */
	public function normaliseVariable(string $name, string $value = ''): array
	{
		// Split our compound string into it's parts.
		if (strpos($name, '=') !== false)
		{
			list($name, $value) = explode('=', $name, 2);
		}

		$name  = trim($name);
		$value = trim($value);

		// Sanitise the name
		$name = str_replace(['export', '\'', '"'], '', $name);

		// Sanitise the value
		$value = $this->sanitiseValue($value);

		$value = $this->resolveNestedVariables($value);

		return [$name, $value];
	}

	//--------------------------------------------------------------------

	/**
	 * Strips quotes from the environment variable value.
	 *
	 * This was borrowed from the excellent phpdotenv with very few changes.
	 * https://github.com/vlucas/phpdotenv
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function sanitiseValue(string $value): string
	{
		if ( ! $value)
		{
			return $value;
		}

		// Does it begin with a quote?
		if ($this->strpbrk($value[0], '"\'') !== false)
		{
			// value starts with a quote
			$quote        = $value[0];
			$regexPattern = sprintf(
				'/^
                %1$s          # match a quote at the start of the value
                (             # capturing sub-pattern used
                 (?:          # we do not need to capture this
                  [^%1$s\\\\] # any character other than a quote or backslash
                  |\\\\\\\\   # or two backslashes together
                  |\\\\%1$s   # or an escaped quote e.g \"
                 )*           # as many characters that match the previous rules
                )             # end of the capturing sub-pattern
                %1$s          # and the closing quote
                .*$           # and discard any string after the closing quote
                /mx',
				$quote
			);
			$value        = preg_replace($regexPattern, '$1', $value);
			$value        = str_replace("\\$quote", $quote, $value);
			$value        = str_replace('\\\\', '\\', $value);
		}
		else
		{
			$parts = explode(' #', $value, 2);

			$value = trim($parts[0]);

			// Unquoted values cannot contain whitespace
			if (preg_match('/\s+/', $value) > 0)
			{
				throw new \InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
			}
		}

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 *  Resolve the nested variables.
	 *
	 * Look for ${varname} patterns in the variable value and replace with an existing
	 * environment variable.
	 *
	 * This was borrowed from the excellent phpdotenv with very few changes.
	 * https://github.com/vlucas/phpdotenv
	 *
	 * @param $value
	 *
	 * @return string
	 */
	protected function resolveNestedVariables(string $value): string
	{
		if (strpos($value, '$') !== false)
		{
			$loader = $this;

			$value = preg_replace_callback(
				'/\${([a-zA-Z0-9_]+)}/',
				function ($matchedPatterns) use ($loader)
				{
					$nestedVariable = $loader->getVariable($matchedPatterns[1]);

					if (is_null($nestedVariable))
					{
						return $matchedPatterns[0];
					}
					else
					{
						return $nestedVariable;
					}
				},
				$value
			);
		}

		return $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Search the different places for environment variables and return first value found.
	 *
	 * This was borrowed from the excellent phpdotenv with very few changes.
	 * https://github.com/vlucas/phpdotenv
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function getVariable(string $name): string
	{
		switch (true)
		{
			case array_key_exists($name, $_ENV):
				return $_ENV[$name];
				break;
			case array_key_exists($name, $_SERVER):
				return $_SERVER[$name];
				break;
			default:
				$value = getenv($name);

				// switch getenv default to null
				return $value === false ? null : $value;
		}
	}

	//--------------------------------------------------------------------

}

