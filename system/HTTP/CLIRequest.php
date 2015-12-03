<?php namespace CodeIgniter\HTTP;

use App\Config\AppConfig;

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
	 * @var array
	 */
	protected $options = [];

	//--------------------------------------------------------------------

	public function __construct(AppConfig $config)
	{
		parent::__construct($config, null);

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
	 * Returns the value for a single CLI option that was passed in.
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function getOption(string $key)
	{
		if (array_key_exists($key, $this->options))
		{
			return $this->options[$key];
		}

		return null;
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
				$value = '"'. $value .'"';
			}

			$out .= "-{$name} $value ";
		}

		return $out;
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
		// Since we're building the options ourselves,
		// we stop adding it to the segments array once
		// we have found the first dash.
		$options_found = false;

		$argc = $this->server('argc', FILTER_SANITIZE_NUMBER_INT);
		$argv = $this->server('argv');

		// We start at 1 since we never want to include index.php
		for ($i = 1; $i < $argc; $i++)
		{
			// If there's no '-' at the beginning of the argument
			// then add it to our segments.
			if ( ! $options_found && strpos($argv[$i], '-') === false)
			{
				$this->segments[] = filter_var($argv[$i], FILTER_SANITIZE_STRING);
				continue;
			}

			$options_found = true;

			if (substr($argv[$i], 0, 1) != '-')
			{
				continue;
			}

			$arg = filter_var(str_replace('-', '', $argv[$i]), FILTER_SANITIZE_STRING);
			$value = null;

			// If the next item starts with a dash it's a value
			if (isset($argv[$i + 1]) && substr($argv[$i + 1], 0, 1) != '-' )
			{
				$value = filter_var($argv[$i + 1], FILTER_SANITIZE_STRING);
				$i++;
			}

			$this->options[$arg] = $value;
		}
	}

	//--------------------------------------------------------------------

}
