<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use Config\App;
use RuntimeException;

/**
 * Represents a request from the command-line. Provides additional
 * tools to interact with that request since CLI requests are not
 * static like HTTP requests might be.
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under.
 *
 * http://fuelphp.com
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

    /**
     * Constructor
     */
    public function __construct(App $config)
    {
        if (! is_cli()) {
            throw new RuntimeException(static::class . ' needs to run from the command line.'); // @codeCoverageIgnore
        }

        parent::__construct($config);

        // Don't terminate the script when the cli's tty goes away
        ignore_user_abort(true);

        $this->parseCommand();
    }

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

    /**
     * Returns an associative array of all CLI options found, with
     * their values.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the path segments.
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Returns the value for a single CLI option that was passed in.
     *
     * @return string|null
     */
    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

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
     */
    public function getOptionString(bool $useLongOpts = false): string
    {
        if (empty($this->options)) {
            return '';
        }

        $out = '';

        foreach ($this->options as $name => $value) {
            if ($useLongOpts && mb_strlen($name) > 1) {
                $out .= "--{$name} ";
            } else {
                $out .= "-{$name} ";
            }

            if ($value === null) {
                continue;
            }

            if (mb_strpos($value, ' ') !== false) {
                $out .= '"' . $value . '" ';
            } else {
                $out .= "{$value} ";
            }
        }

        return trim($out);
    }

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

        foreach ($args as $i => $arg) {
            if (mb_strpos($arg, '-') !== 0) {
                if ($optionValue) {
                    $optionValue = false;
                } else {
                    $this->segments[] = $arg;
                }

                continue;
            }

            $arg   = ltrim($arg, '-');
            $value = null;

            if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0) {
                $value       = $args[$i + 1];
                $optionValue = true;
            }

            $this->options[$arg] = $value;
        }
    }

    /**
     * Determines if this request was made from the command line (CLI).
     */
    public function isCLI(): bool
    {
        return is_cli();
    }
}
