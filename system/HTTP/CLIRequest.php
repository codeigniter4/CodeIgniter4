<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Exceptions\RuntimeException;
use Config\App;
use Locale;

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
 *
 * @see \CodeIgniter\HTTP\CLIRequestTest
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
     * Command line arguments (segments and options).
     *
     * @var array
     */
    protected $args = [];

    /**
     * Set the expected HTTP verb
     *
     * @var string
     */
    protected $method = 'CLI';

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

        // Set SiteURI for this request
        $this->uri = new SiteURI($config, $this->getPath());
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
        return implode('/', $this->segments);
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
     * Returns an array of all CLI arguments (segments and options).
     */
    public function getArgs(): array
    {
        return $this->args;
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
        if ($this->options === []) {
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
     *
     * @return void
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
                    $this->args[]     = $arg;
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
            $this->args[$arg]    = $value;
        }
    }

    /**
     * Determines if this request was made from the command line (CLI).
     */
    public function isCLI(): bool
    {
        return true;
    }

    /**
     * Fetch an item from GET data.
     *
     * @param array|string|null $index  Index for item to fetch from $_GET.
     * @param int|null          $filter A filter name to apply.
     * @param array|int|null    $flags
     *
     * @return array|null
     */
    public function getGet($index = null, $filter = null, $flags = null)
    {
        return $this->returnNullOrEmptyArray($index);
    }

    /**
     * Fetch an item from POST.
     *
     * @param array|string|null $index  Index for item to fetch from $_POST.
     * @param int|null          $filter A filter name to apply
     * @param array|int|null    $flags
     *
     * @return array|null
     */
    public function getPost($index = null, $filter = null, $flags = null)
    {
        return $this->returnNullOrEmptyArray($index);
    }

    /**
     * Fetch an item from POST data with fallback to GET.
     *
     * @param array|string|null $index  Index for item to fetch from $_POST or $_GET
     * @param int|null          $filter A filter name to apply
     * @param array|int|null    $flags
     *
     * @return array|null
     */
    public function getPostGet($index = null, $filter = null, $flags = null)
    {
        return $this->returnNullOrEmptyArray($index);
    }

    /**
     * Fetch an item from GET data with fallback to POST.
     *
     * @param array|string|null $index  Index for item to be fetched from $_GET or $_POST
     * @param int|null          $filter A filter name to apply
     * @param array|int|null    $flags
     *
     * @return array|null
     */
    public function getGetPost($index = null, $filter = null, $flags = null)
    {
        return $this->returnNullOrEmptyArray($index);
    }

    /**
     * This is a place holder for calls from cookie_helper get_cookie().
     *
     * @param array|string|null $index  Index for item to be fetched from $_COOKIE
     * @param int|null          $filter A filter name to be applied
     * @param mixed             $flags
     *
     * @return array|null
     */
    public function getCookie($index = null, $filter = null, $flags = null)
    {
        return $this->returnNullOrEmptyArray($index);
    }

    /**
     * @param array|string|null $index
     *
     * @return array|null
     */
    private function returnNullOrEmptyArray($index)
    {
        return ($index === null || is_array($index)) ? [] : null;
    }

    /**
     * Gets the current locale, with a fallback to the default
     * locale if none is set.
     */
    public function getLocale(): string
    {
        return Locale::getDefault();
    }

    /**
     * Checks this request type.
     *
     * @param         string                                                                    $type HTTP verb or 'json' or 'ajax'
     * @phpstan-param string|'get'|'post'|'put'|'delete'|'head'|'patch'|'options'|'json'|'ajax' $type
     */
    public function is(string $type): bool
    {
        return false;
    }
}
