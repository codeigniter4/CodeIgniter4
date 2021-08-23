<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Factories;
use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\TestLogger;
use Config\App;
use Config\Database;
use Config\Logger;
use Config\Services;
use Config\View;
use Laminas\Escaper\Escaper;

// Services Convenience Functions

if (! function_exists('app_timezone')) {
    /**
     * Returns the timezone the application has been set to display
     * dates in. This might be different than the timezone set
     * at the server level, as you often want to stores dates in UTC
     * and convert them on the fly for the user.
     */
    function app_timezone(): string
    {
        $config = config(App::class);

        return $config->appTimezone;
    }
}

if (! function_exists('cache')) {
    /**
     * A convenience method that provides access to the Cache
     * object. If no parameter is provided, will return the object,
     * otherwise, will attempt to return the cached value.
     *
     * Examples:
     *    cache()->save('foo', 'bar');
     *    $foo = cache('bar');
     *
     * @return CacheInterface|mixed
     */
    function cache(?string $key = null)
    {
        $cache = Services::cache();

        // No params - return cache object
        if ($key === null) {
            return $cache;
        }

        // Still here? Retrieve the value.
        return $cache->get($key);
    }
}

if (! function_exists('clean_path')) {
    /**
     * A convenience method to clean paths for
     * a nicer looking output. Useful for exception
     * handling, error logging, etc.
     */
    function clean_path(string $path): string
    {
        // Resolve relative paths
        $path = realpath($path) ?: $path;

        switch (true) {
            case strpos($path, APPPATH) === 0:
                return 'APPPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(APPPATH));

            case strpos($path, SYSTEMPATH) === 0:
                return 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(SYSTEMPATH));

            case strpos($path, FCPATH) === 0:
                return 'FCPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(FCPATH));

            case defined('VENDORPATH') && strpos($path, VENDORPATH) === 0:
                return 'VENDORPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(VENDORPATH));

            case strpos($path, ROOTPATH) === 0:
                return 'ROOTPATH' . DIRECTORY_SEPARATOR . substr($path, strlen(ROOTPATH));

            default:
                return $path;
        }
    }
}

if (! function_exists('command')) {
    /**
     * Runs a single command.
     * Input expected in a single string as would
     * be used on the command line itself:
     *
     *  > command('migrate:create SomeMigration');
     *
     * @return false|string
     */
    function command(string $command)
    {
        $runner      = service('commands');
        $regexString = '([^\s]+?)(?:\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
        $regexQuoted = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

        $args   = [];
        $length = strlen($command);
        $cursor = 0;

        /**
         * Adopted from Symfony's `StringInput::tokenize()` with few changes.
         *
         * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Input/StringInput.php
         */
        while ($cursor < $length) {
            if (preg_match('/\s+/A', $command, $match, 0, $cursor)) {
                // nothing to do
            } elseif (preg_match('/' . $regexQuoted . '/A', $command, $match, 0, $cursor)) {
                $args[] = stripcslashes(substr($match[0], 1, strlen($match[0]) - 2));
            } elseif (preg_match('/' . $regexString . '/A', $command, $match, 0, $cursor)) {
                $args[] = stripcslashes($match[1]);
            } else {
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException(sprintf('Unable to parse input near "... %s ...".', substr($command, $cursor, 10)));
                // @codeCoverageIgnoreEnd
            }

            $cursor += strlen($match[0]);
        }

        $command     = array_shift($args);
        $params      = [];
        $optionValue = false;

        foreach ($args as $i => $arg) {
            if (mb_strpos($arg, '-') !== 0) {
                if ($optionValue) {
                    // if this was an option value, it was already
                    // included in the previous iteration
                    $optionValue = false;
                } else {
                    // add to segments if not starting with '-'
                    // and not an option value
                    $params[] = $arg;
                }

                continue;
            }

            $arg   = ltrim($arg, '-');
            $value = null;

            if (isset($args[$i + 1]) && mb_strpos($args[$i + 1], '-') !== 0) {
                $value       = $args[$i + 1];
                $optionValue = true;
            }

            $params[$arg] = $value;
        }

        ob_start();
        $runner->run($command, $params);

        return ob_get_clean();
    }
}

if (! function_exists('config')) {
    /**
     * More simple way of getting config instances from Factories
     *
     * @return mixed
     */
    function config(string $name, bool $getShared = true)
    {
        return Factories::config($name, ['getShared' => $getShared]);
    }
}

if (! function_exists('cookie')) {
    /**
     * Simpler way to create a new Cookie instance.
     *
     * @param string $name    Name of the cookie
     * @param string $value   Value of the cookie
     * @param array  $options Array of options to be passed to the cookie
     *
     * @throws CookieException
     */
    function cookie(string $name, string $value = '', array $options = []): Cookie
    {
        return new Cookie($name, $value, $options);
    }
}

if (! function_exists('cookies')) {
    /**
     * Fetches the global `CookieStore` instance held by `Response`.
     *
     * @param Cookie[] $cookies   If `getGlobal` is false, this is passed to CookieStore's constructor
     * @param bool     $getGlobal If false, creates a new instance of CookieStore
     */
    function cookies(array $cookies = [], bool $getGlobal = true): CookieStore
    {
        if ($getGlobal) {
            return Services::response()->getCookieStore();
        }

        return new CookieStore($cookies);
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Returns the CSRF token name.
     * Can be used in Views when building hidden inputs manually,
     * or used in javascript vars when using APIs.
     */
    function csrf_token(): string
    {
        return Services::security()->getTokenName();
    }
}

if (! function_exists('csrf_header')) {
    /**
     * Returns the CSRF header name.
     * Can be used in Views by adding it to the meta tag
     * or used in javascript to define a header name when using APIs.
     */
    function csrf_header(): string
    {
        return Services::security()->getHeaderName();
    }
}

if (! function_exists('csrf_hash')) {
    /**
     * Returns the current hash value for the CSRF protection.
     * Can be used in Views when building hidden inputs manually,
     * or used in javascript vars for API usage.
     */
    function csrf_hash(): string
    {
        return Services::security()->getHash();
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Generates a hidden input field for use within manually generated forms.
     */
    function csrf_field(?string $id = null): string
    {
        return '<input type="hidden"' . (! empty($id) ? ' id="' . esc($id, 'attr') . '"' : '') . ' name="' . csrf_token() . '" value="' . csrf_hash() . '" />';
    }
}

if (! function_exists('csrf_meta')) {
    /**
     * Generates a meta tag for use within javascript calls.
     */
    function csrf_meta(?string $id = null): string
    {
        return '<meta' . (! empty($id) ? ' id="' . esc($id, 'attr') . '"' : '') . ' name="' . csrf_header() . '" content="' . csrf_hash() . '" />';
    }
}

if (! function_exists('db_connect')) {
    /**
     * Grabs a database connection and returns it to the user.
     *
     * This is a convenience wrapper for \Config\Database::connect()
     * and supports the same parameters. Namely:
     *
     * When passing in $db, you may pass any of the following to connect:
     * - group name
     * - existing connection instance
     * - array of database configuration values
     *
     * If $getShared === false then a new connection instance will be provided,
     * otherwise it will all calls will return the same instance.
     *
     * @param array|ConnectionInterface|string|null $db
     *
     * @return BaseConnection
     */
    function db_connect($db = null, bool $getShared = true)
    {
        return Database::connect($db, $getShared);
    }
}

if (! function_exists('dd')) {
    /**
     * Prints a Kint debug report and exits.
     *
     * @param array ...$vars
     *
     * @codeCoverageIgnore Can't be tested ... exits
     */
    function dd(...$vars)
    {
        // @codeCoverageIgnoreStart
        Kint::$aliases[] = 'dd';
        Kint::dump(...$vars);

        exit;
        // @codeCoverageIgnoreEnd
    }
}

if (! function_exists('env')) {
    /**
     * Allows user to retrieve values from the environment
     * variables that have been set. Especially useful for
     * retrieving values set from the .env file for
     * use in config files.
     *
     * @param string|null $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        // Not found? Return the default value
        if ($value === false) {
            return $default;
        }

        // Handle any boolean values
        switch (strtolower($value)) {
            case 'true':
                return true;

            case 'false':
                return false;

            case 'empty':
                return '';

            case 'null':
                return null;
        }

        return $value;
    }
}

if (! function_exists('esc')) {
    /**
     * Performs simple auto-escaping of data for security reasons.
     * Might consider making this more complex at a later date.
     *
     * If $data is a string, then it simply escapes and returns it.
     * If $data is an array, then it loops over it, escaping each
     * 'value' of the key/value pairs.
     *
     * Valid context values: html, js, css, url, attr, raw, null
     *
     * @param array|string $data
     * @param string       $encoding
     *
     * @throws InvalidArgumentException
     *
     * @return array|string
     */
    function esc($data, string $context = 'html', ?string $encoding = null)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = esc($value, $context);
            }
        }

        if (is_string($data)) {
            $context = strtolower($context);

            // Provide a way to NOT escape data since
            // this could be called automatically by
            // the View library.
            if (empty($context) || $context === 'raw') {
                return $data;
            }

            if (! in_array($context, ['html', 'js', 'css', 'url', 'attr'], true)) {
                throw new InvalidArgumentException('Invalid escape context provided.');
            }

            $method = $context === 'attr' ? 'escapeHtmlAttr' : 'escape' . ucfirst($context);

            static $escaper;
            if (! $escaper) {
                $escaper = new Escaper($encoding);
            }

            if ($encoding && $escaper->getEncoding() !== $encoding) {
                $escaper = new Escaper($encoding);
            }

            $data = $escaper->{$method}($data);
        }

        return $data;
    }
}

if (! function_exists('force_https')) {
    /**
     * Used to force a page to be accessed in via HTTPS.
     * Uses a standard redirect, plus will set the HSTS header
     * for modern browsers that support, which gives best
     * protection against man-in-the-middle attacks.
     *
     * @see https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
     *
     * @param int               $duration How long should the SSL header be set for? (in seconds)
     *                                    Defaults to 1 year.
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @throws HTTPException
     */
    function force_https(int $duration = 31536000, ?RequestInterface $request = null, ?ResponseInterface $response = null)
    {
        if ($request === null) {
            $request = Services::request(null, true);
        }
        if ($response === null) {
            $response = Services::response(null, true);
        }

        if ((ENVIRONMENT !== 'testing' && (is_cli() || $request->isSecure())) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'test')) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        // If the session status is active, we should regenerate
        // the session ID for safety sake.
        if (ENVIRONMENT !== 'testing' && session_status() === PHP_SESSION_ACTIVE) {
            // @codeCoverageIgnoreStart
            Services::session(null, true)
                ->regenerate();
            // @codeCoverageIgnoreEnd
        }

        $baseURL = config(App::class)->baseURL;

        if (strpos($baseURL, 'https://') === 0) {
            $baseURL = substr($baseURL, strlen('https://'));
        } elseif (strpos($baseURL, 'http://') === 0) {
            $baseURL = substr($baseURL, strlen('http://'));
        }

        $uri = URI::createURIString(
            'https',
            $baseURL,
            $request->uri->getPath(), // Absolute URIs should use a "/" for an empty path
            $request->uri->getQuery(),
            $request->uri->getFragment()
        );

        // Set an HSTS header
        $response->setHeader('Strict-Transport-Security', 'max-age=' . $duration);
        $response->redirect($uri);
        $response->sendHeaders();

        if (ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            exit();
            // @codeCoverageIgnoreEnd
        }
    }
}

if (! function_exists('function_usable')) {
    /**
     * Function usable
     *
     * Executes a function_exists() check, and if the Suhosin PHP
     * extension is loaded - checks whether the function that is
     * checked might be disabled in there as well.
     *
     * This is useful as function_exists() will return FALSE for
     * functions disabled via the *disable_functions* php.ini
     * setting, but not for *suhosin.executor.func.blacklist* and
     * *suhosin.executor.disable_eval*. These settings will just
     * terminate script execution if a disabled function is executed.
     *
     * The above described behavior turned out to be a bug in Suhosin,
     * but even though a fix was committed for 0.9.34 on 2012-02-12,
     * that version is yet to be released. This function will therefore
     * be just temporary, but would probably be kept for a few years.
     *
     * @see   http://www.hardened-php.net/suhosin/
     *
     * @param string $functionName Function to check for
     *
     * @return bool TRUE if the function exists and is safe to call,
     *              FALSE otherwise.
     *
     * @codeCoverageIgnore This is too exotic
     */
    function function_usable(string $functionName): bool
    {
        static $_suhosin_func_blacklist;

        if (function_exists($functionName)) {
            if (! isset($_suhosin_func_blacklist)) {
                $_suhosin_func_blacklist = extension_loaded('suhosin') ? explode(',', trim(ini_get('suhosin.executor.func.blacklist'))) : [];
            }

            return ! in_array($functionName, $_suhosin_func_blacklist, true);
        }

        return false;
    }
}

if (! function_exists('helper')) {
    /**
     * Loads a helper file into memory. Supports namespaced helpers,
     * both in and out of the 'helpers' directory of a namespaced directory.
     *
     * Will load ALL helpers of the matching name, in the following order:
     *   1. app/Helpers
     *   2. {namespace}/Helpers
     *   3. system/Helpers
     *
     * @param array|string $filenames
     *
     * @throws FileNotFoundException
     */
    function helper($filenames)
    {
        static $loaded = [];

        $loader = Services::locator(true);

        if (! is_array($filenames)) {
            $filenames = [$filenames];
        }

        // Store a list of all files to include...
        $includes = [];

        foreach ($filenames as $filename) {
            // Store our system and application helper
            // versions so that we can control the load ordering.
            $systemHelper  = null;
            $appHelper     = null;
            $localIncludes = [];

            if (strpos($filename, '_helper') === false) {
                $filename .= '_helper';
            }

            // Check if this helper has already been loaded
            if (in_array($filename, $loaded, true)) {
                continue;
            }

            // If the file is namespaced, we'll just grab that
            // file and not search for any others
            if (strpos($filename, '\\') !== false) {
                $path = $loader->locateFile($filename, 'Helpers');

                if (empty($path)) {
                    throw FileNotFoundException::forFileNotFound($filename);
                }

                $includes[] = $path;
                $loaded[]   = $filename;
            }

            // No namespaces, so search in all available locations
            else {
                $paths = $loader->search('Helpers/' . $filename);

                foreach ($paths as $path) {
                    if (strpos($path, APPPATH) === 0) {
                        // @codeCoverageIgnoreStart
                        $appHelper = $path;
                    // @codeCoverageIgnoreEnd
                    } elseif (strpos($path, SYSTEMPATH) === 0) {
                        $systemHelper = $path;
                    } else {
                        $localIncludes[] = $path;
                        $loaded[]        = $filename;
                    }
                }

                // App-level helpers should override all others
                if (! empty($appHelper)) {
                    // @codeCoverageIgnoreStart
                    $includes[] = $appHelper;
                    $loaded[]   = $filename;
                    // @codeCoverageIgnoreEnd
                }

                // All namespaced files get added in next
                $includes = array_merge($includes, $localIncludes);

                // And the system default one should be added in last.
                if (! empty($systemHelper)) {
                    $includes[] = $systemHelper;
                    $loaded[]   = $filename;
                }
            }
        }

        // Now actually include all of the files
        foreach ($includes as $path) {
            include_once $path;
        }
    }
}

if (! function_exists('is_cli')) {
    /**
     * Check if PHP was invoked from the command line.
     *
     * @codeCoverageIgnore Cannot be tested fully as PHPUnit always run in CLI
     */
    function is_cli(): bool
    {
        if (PHP_SAPI === 'cli') {
            return true;
        }

        if (defined('STDIN')) {
            return true;
        }

        if (stristr(PHP_SAPI, 'cgi') && getenv('TERM')) {
            return true;
        }

        if (! isset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']) && isset($_SERVER['argv']) && count($_SERVER['argv']) > 0) {
            return true;
        }

        // if source of request is from CLI, the `$_SERVER` array will not populate this key
        return ! isset($_SERVER['REQUEST_METHOD']);
    }
}

if (! function_exists('is_really_writable')) {
    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @see https://bugs.php.net/bug.php?id=54709
     *
     * @throws Exception
     *
     * @codeCoverageIgnore Not practical to test, as travis runs on linux
     */
    function is_really_writable(string $file): bool
    {
        // If we're on a Unix server we call is_writable
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . bin2hex(random_bytes(16));
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }

            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);

            return true;
        }

        if (! is_file($file) || ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }

        fclose($fp);

        return true;
    }
}

if (! function_exists('lang')) {
    /**
     * A convenience method to translate a string or array of them and format
     * the result with the intl extension's MessageFormatter.
     *
     * @return string
     */
    function lang(string $line, array $args = [], ?string $locale = null)
    {
        return Services::language($locale)
            ->getLine($line, $args);
    }
}

if (! function_exists('log_message')) {
    /**
     * A convenience/compatibility method for logging events through
     * the Log system.
     *
     * Allowed log levels are:
     *  - emergency
     *  - alert
     *  - critical
     *  - error
     *  - warning
     *  - notice
     *  - info
     *  - debug
     *
     * @return mixed
     */
    function log_message(string $level, string $message, array $context = [])
    {
        // When running tests, we want to always ensure that the
        // TestLogger is running, which provides utilities for
        // for asserting that logs were called in the test code.
        if (ENVIRONMENT === 'testing') {
            $logger = new TestLogger(new Logger());

            return $logger->log($level, $message, $context);
        }

        // @codeCoverageIgnoreStart
        return Services::logger(true)
            ->log($level, $message, $context);
        // @codeCoverageIgnoreEnd
    }
}

if (! function_exists('model')) {
    /**
     * More simple way of getting model instances from Factories
     *
     * @return mixed
     */
    function model(string $name, bool $getShared = true, ?ConnectionInterface &$conn = null)
    {
        return Factories::models($name, ['getShared' => $getShared], $conn);
    }
}

if (! function_exists('old')) {
    /**
     * Provides access to "old input" that was set in the session
     * during a redirect()->withInput().
     *
     * @param null        $default
     * @param bool|string $escape
     *
     * @return mixed|null
     */
    function old(string $key, $default = null, $escape = 'html')
    {
        // Ensure the session is loaded
        if (session_status() === PHP_SESSION_NONE && ENVIRONMENT !== 'testing') {
            // @codeCoverageIgnoreStart
            session();
            // @codeCoverageIgnoreEnd
        }

        $request = Services::request();

        $value = $request->getOldInput($key);

        // Return the default value if nothing
        // found in the old input.
        if ($value === null) {
            return $default;
        }

        // If the result was serialized array or string, then unserialize it for use...
        if (is_string($value) && (strpos($value, 'a:') === 0 || strpos($value, 's:') === 0)) {
            $value = unserialize($value);
        }

        return $escape === false ? $value : esc($value, $escape);
    }
}

if (! function_exists('redirect')) {
    /**
     * Convenience method that works with the current global $request and
     * $router instances to redirect using named/reverse-routed routes
     * to determine the URL to go to. If nothing is found, will treat
     * as a traditional redirect and pass the string in, letting
     * $response->redirect() determine the correct method and code.
     *
     * If more control is needed, you must use $response->redirect explicitly.
     *
     * @param string $route
     */
    function redirect(?string $route = null): RedirectResponse
    {
        $response = Services::redirectresponse(null, true);

        if (! empty($route)) {
            return $response->route($route);
        }

        return $response;
    }
}

if (! function_exists('remove_invisible_characters')) {
    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     */
    function remove_invisible_characters(string $str, bool $urlEncoded = true): string
    {
        $nonDisplayables = [];

        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($urlEncoded) {
            $nonDisplayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
            $nonDisplayables[] = '/%1[0-9a-f]/';   // url encoded 16-31
        }

        $nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($nonDisplayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }
}

if (! function_exists('route_to')) {
    /**
     * Given a controller/method string and any params,
     * will attempt to build the relative URL to the
     * matching route.
     *
     * NOTE: This requires the controller/method to
     * have a route defined in the routes Config file.
     *
     * @param mixed ...$params
     *
     * @return false|string
     */
    function route_to(string $method, ...$params)
    {
        return Services::routes()->reverseRoute($method, ...$params);
    }
}

if (! function_exists('session')) {
    /**
     * A convenience method for accessing the session instance,
     * or an item that has been set in the session.
     *
     * Examples:
     *    session()->set('foo', 'bar');
     *    $foo = session('bar');
     *
     * @param string $val
     *
     * @return mixed|Session|null
     */
    function session(?string $val = null)
    {
        $session = Services::session();

        // Returning a single item?
        if (is_string($val)) {
            return $session->get($val);
        }

        return $session;
    }
}

if (! function_exists('service')) {
    /**
     * Allows cleaner access to the Services Config file.
     * Always returns a SHARED instance of the class, so
     * calling the function multiple times should always
     * return the same instance.
     *
     * These are equal:
     *  - $timer = service('timer')
     *  - $timer = \CodeIgniter\Config\Services::timer();
     *
     * @param mixed ...$params
     *
     * @return mixed
     */
    function service(string $name, ...$params)
    {
        return Services::$name(...$params);
    }
}

if (! function_exists('single_service')) {
    /**
     * Always returns a new instance of the class.
     *
     * @param mixed ...$params
     *
     * @return mixed
     */
    function single_service(string $name, ...$params)
    {
        $service = Services::serviceExists($name);

        if ($service === null) {
            // The service is not defined anywhere so just return.
            return null;
        }

        $method = new ReflectionMethod($service, $name);
        $count  = $method->getNumberOfParameters();
        $mParam = $method->getParameters();
        $params = $params ?? [];

        if ($count === 1) {
            // This service needs only one argument, which is the shared
            // instance flag, so let's wrap up and pass false here.
            return $service::$name(false);
        }

        // Fill in the params with the defaults, but stop before the last
        for ($startIndex = count($params); $startIndex <= $count - 2; $startIndex++) {
            $params[$startIndex] = $mParam[$startIndex]->getDefaultValue();
        }

        // Ensure the last argument will not create a shared instance
        $params[$count - 1] = false;

        return $service::$name(...$params);
    }
}

if (! function_exists('slash_item')) {
    // Unlike CI3, this function is placed here because
    // it's not a config, or part of a config.
    /**
     * Fetch a config file item with slash appended (if not empty)
     *
     * @param string $item Config item name
     *
     * @return string|null The configuration item or NULL if
     *                     the item doesn't exist
     */
    function slash_item(string $item): ?string
    {
        $config     = config(App::class);
        $configItem = $config->{$item};

        if (! isset($configItem) || empty(trim($configItem))) {
            return $configItem;
        }

        return rtrim($configItem, '/') . '/';
    }
}

if (! function_exists('stringify_attributes')) {
    /**
     * Stringify attributes for use in HTML tags.
     *
     * Helper function used to convert a string, array, or object
     * of attributes to a string.
     *
     * @param mixed $attributes string, array, object
     */
    function stringify_attributes($attributes, bool $js = false): string
    {
        $atts = '';

        if (empty($attributes)) {
            return $atts;
        }

        if (is_string($attributes)) {
            return ' ' . $attributes;
        }

        $attributes = (array) $attributes;

        foreach ($attributes as $key => $val) {
            $atts .= ($js) ? $key . '=' . esc($val, 'js') . ',' : ' ' . $key . '="' . esc($val) . '"';
        }

        return rtrim($atts, ',');
    }
}

if (! function_exists('timer')) {
    /**
     * A convenience method for working with the timer.
     * If no parameter is passed, it will return the timer instance,
     * otherwise will start or stop the timer intelligently.
     *
     * @return mixed|Timer
     */
    function timer(?string $name = null)
    {
        $timer = Services::timer();

        if (empty($name)) {
            return $timer;
        }

        if ($timer->has($name)) {
            return $timer->stop($name);
        }

        return $timer->start($name);
    }
}

if (! function_exists('trace')) {
    /**
     * Provides a backtrace to the current execution point, from Kint.
     */
    function trace()
    {
        Kint::$aliases[] = 'trace';
        Kint::trace();
    }
}

if (! function_exists('view')) {
    /**
     * Grabs the current RendererInterface-compatible class
     * and tells it to render the specified view. Simply provides
     * a convenience method that can be used in Controllers,
     * libraries, and routed closures.
     *
     * NOTE: Does not provide any escaping of the data, so that must
     * all be handled manually by the developer.
     *
     * @param array $options Unused - reserved for third-party extensions.
     */
    function view(string $name, array $data = [], array $options = []): string
    {
        /**
         * @var CodeIgniter\View\View $renderer
         */
        $renderer = Services::renderer();

        $saveData = config(View::class)->saveData;

        if (array_key_exists('saveData', $options)) {
            $saveData = (bool) $options['saveData'];
            unset($options['saveData']);
        }

        return $renderer->setData($data, 'raw')->render($name, $options, $saveData);
    }
}

if (! function_exists('view_cell')) {
    /**
     * View cells are used within views to insert HTML chunks that are managed
     * by other classes.
     *
     * @param null $params
     *
     * @throws ReflectionException
     */
    function view_cell(string $library, $params = null, int $ttl = 0, ?string $cacheName = null): string
    {
        return Services::viewcell()
            ->render($library, $params, $ttl, $cacheName);
    }
}

/**
 * These helpers come from Laravel so will not be
 * re-tested and can be ignored safely.
 *
 * @see https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/helpers.php
 */
if (! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param object|string $class
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|string $class
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        // @phpstan-ignore-next-line
        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (! function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param string $trait
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}
