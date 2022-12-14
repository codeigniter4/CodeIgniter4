<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Autoloader;

use CodeIgniter\Exceptions\ConfigException;
use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Config\Autoload;
use Config\Modules;
use InvalidArgumentException;
use RuntimeException;

/**
 * An autoloader that uses both PSR4 autoloading, and traditional classmaps.
 *
 * Given a foo-bar package of classes in the file system at the following paths:
 * ```
 *      /path/to/packages/foo-bar/
 *          /src
 *              Baz.php         # Foo\Bar\Baz
 *              Qux/
 *                  Quux.php    # Foo\Bar\Qux\Quux
 * ```
 * you can add the path to the configuration array that is passed in the constructor.
 * The Config array consists of 2 primary keys, both of which are associative arrays:
 * 'psr4', and 'classmap'.
 * ```
 *      $Config = [
 *          'psr4' => [
 *              'Foo\Bar'   => '/path/to/packages/foo-bar'
 *          ],
 *          'classmap' => [
 *              'MyClass'   => '/path/to/class/file.php'
 *          ]
 *      ];
 * ```
 * Example:
 * ```
 *      <?php
 *      // our configuration array
 *      $Config = [ ... ];
 *      $loader = new \CodeIgniter\Autoloader\Autoloader($Config);
 *
 *      // register the autoloader
 *      $loader->register();
 * ```
 */
class Autoloader
{
    /**
     * Stores namespaces as key, and path as values.
     *
     * @var array<string, array<string>>
     */
    protected $prefixes = [];

    /**
     * Stores class name as key, and path as values.
     *
     * @var array<string, string>
     */
    protected $classmap = [];

    /**
     * Stores files as a list.
     *
     * @var string[]
     * @phpstan-var list<string>
     */
    protected $files = [];

    /**
     * Stores helper list.
     * Always load the URL helper, it should be used in most apps.
     *
     * @var string[]
     * @phpstan-var list<string>
     */
    protected $helpers = ['url'];

    /**
     * Reads in the configuration array (described above) and stores
     * the valid parts that we'll need.
     *
     * @return $this
     */
    public function initialize(Autoload $config, Modules $modules)
    {
        $this->prefixes = [];
        $this->classmap = [];
        $this->files    = [];

        // We have to have one or the other, though we don't enforce the need
        // to have both present in order to work.
        if ($config->psr4 === [] && $config->classmap === []) {
            throw new InvalidArgumentException('Config array must contain either the \'psr4\' key or the \'classmap\' key.');
        }

        if ($config->psr4 !== []) {
            $this->addNamespace($config->psr4);
        }

        if ($config->classmap !== []) {
            $this->classmap = $config->classmap;
        }

        if ($config->files !== []) {
            $this->files = $config->files;
        }

        if (isset($config->helpers)) { // @phpstan-ignore-line
            $this->helpers = [...$this->helpers, ...$config->helpers];
        }

        if (is_file(COMPOSER_PATH)) {
            $this->loadComposerInfo($modules);
        }

        return $this;
    }

    private function loadComposerInfo(Modules $modules): void
    {
        /**
         * @var ClassLoader $composer
         */
        $composer = include COMPOSER_PATH;

        $this->loadComposerClassmap($composer);

        // Should we load through Composer's namespaces, also?
        if ($modules->discoverInComposer) {
            // @phpstan-ignore-next-line
            $this->loadComposerNamespaces($composer, $modules->composerPackages ?? []);
        }

        unset($composer);
    }

    /**
     * Register the loader with the SPL autoloader stack.
     */
    public function register()
    {
        // Prepend the PSR4  autoloader for maximum performance.
        spl_autoload_register([$this, 'loadClass'], true, true);

        // Now prepend another loader for the files in our class map.
        spl_autoload_register([$this, 'loadClassmap'], true, true);

        // Load our non-class files
        foreach ($this->files as $file) {
            $this->includeFile($file);
        }
    }

    /**
     * Unregister autoloader.
     *
     * This method is for testing.
     */
    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadClass']);
        spl_autoload_unregister([$this, 'loadClassmap']);
    }

    /**
     * Registers namespaces with the autoloader.
     *
     * @param array<string, array<int, string>|string>|string $namespace
     * @phpstan-param array<string, list<string>|string>|string $namespace
     *
     * @return $this
     */
    public function addNamespace($namespace, ?string $path = null)
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $namespacedPath) {
                $prefix = trim($prefix, '\\');

                if (is_array($namespacedPath)) {
                    foreach ($namespacedPath as $dir) {
                        $this->prefixes[$prefix][] = rtrim($dir, '\\/') . DIRECTORY_SEPARATOR;
                    }

                    continue;
                }

                $this->prefixes[$prefix][] = rtrim($namespacedPath, '\\/') . DIRECTORY_SEPARATOR;
            }
        } else {
            $this->prefixes[trim($namespace, '\\')][] = rtrim($path, '\\/') . DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Get namespaces with prefixes as keys and paths as values.
     *
     * If a prefix param is set, returns only paths to the given prefix.
     *
     * @return array
     */
    public function getNamespace(?string $prefix = null)
    {
        if ($prefix === null) {
            return $this->prefixes;
        }

        return $this->prefixes[trim($prefix, '\\')] ?? [];
    }

    /**
     * Removes a single namespace from the psr4 settings.
     *
     * @return $this
     */
    public function removeNamespace(string $namespace)
    {
        if (isset($this->prefixes[trim($namespace, '\\')])) {
            unset($this->prefixes[trim($namespace, '\\')]);
        }

        return $this;
    }

    /**
     * Load a class using available class mapping.
     *
     * @return false|string
     */
    public function loadClassmap(string $class)
    {
        $file = $this->classmap[$class] ?? '';

        if (is_string($file) && $file !== '') {
            return $this->includeFile($file);
        }

        return false;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully qualified class name.
     *
     * @return false|string The mapped file on success, or boolean false
     *                      on failure.
     */
    public function loadClass(string $class)
    {
        $class = trim($class, '\\');
        $class = str_ireplace('.php', '', $class);

        return $this->loadInNamespace($class);
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name
     *
     * @return false|string The mapped file name on success, or boolean false on fail
     */
    protected function loadInNamespace(string $class)
    {
        if (strpos($class, '\\') === false) {
            return false;
        }

        foreach ($this->prefixes as $namespace => $directories) {
            foreach ($directories as $directory) {
                $directory = rtrim($directory, '\\/');

                if (strpos($class, $namespace) === 0) {
                    $filePath = $directory . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))) . '.php';
                    $filename = $this->includeFile($filePath);

                    if ($filename) {
                        return $filename;
                    }
                }
            }
        }

        // never found a mapped file
        return false;
    }

    /**
     * A central way to include a file. Split out primarily for testing purposes.
     *
     * @return false|string The filename on success, false if the file is not loaded
     */
    protected function includeFile(string $file)
    {
        $file = $this->sanitizeFilename($file);

        if (is_file($file)) {
            include_once $file;

            return $file;
        }

        return false;
    }

    /**
     * Check file path.
     *
     * Checks special characters that are illegal in filenames on certain
     * operating systems and special characters requiring special escaping
     * to manipulate at the command line. Replaces spaces and consecutive
     * dashes with a single dash. Trim period, dash and underscore from beginning
     * and end of filename.
     *
     * @return string The sanitized filename
     */
    public function sanitizeFilename(string $filename): string
    {
        // Only allow characters deemed safe for POSIX portable filenames.
        // Plus the forward slash for directory separators since this might be a path.
        // http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap03.html#tag_03_278
        // Modified to allow backslash and colons for on Windows machines.
        $result = preg_match_all('/[^0-9\p{L}\s\/\-_.:\\\\]/u', $filename, $matches);

        if ($result > 0) {
            $chars = implode('', $matches[0]);

            throw new InvalidArgumentException(
                'The file path contains special characters "' . $chars
                . '" that are not allowed: "' . $filename . '"'
            );
        }
        if ($result === false) {
            if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
                $message = preg_last_error_msg();
            } else {
                $message = 'Regex error. error code: ' . preg_last_error();
            }

            throw new RuntimeException($message . '. filename: "' . $filename . '"');
        }

        // Clean up our filename edges.
        $cleanFilename = trim($filename, '.-_');

        if ($filename !== $cleanFilename) {
            throw new InvalidArgumentException('The characters ".-_" are not allowed in filename edges: "' . $filename . '"');
        }

        return $cleanFilename;
    }

    private function loadComposerNamespaces(ClassLoader $composer, array $composerPackages): void
    {
        $namespacePaths = $composer->getPrefixesPsr4();

        // Get rid of CodeIgniter so we don't have duplicates
        if (isset($namespacePaths['CodeIgniter\\'])) {
            unset($namespacePaths['CodeIgniter\\']);
        }

        $packageList = InstalledVersions::getAllRawData()[0]['versions'];

        // Check config for $composerPackages.
        $only    = $composerPackages['only'] ?? [];
        $exclude = $composerPackages['exclude'] ?? [];
        if ($only !== [] && $exclude !== []) {
            throw new ConfigException('Cannot use "only" and "exclude" at the same time in "Config\Modules::$composerPackages".');
        }

        // Get install paths of packages to add namespace for auto-discovery.
        $installPaths = [];
        if ($only !== []) {
            foreach ($packageList as $packageName => $data) {
                if (in_array($packageName, $only, true) && isset($data['install_path'])) {
                    $installPaths[] = $data['install_path'];
                }
            }
        } else {
            foreach ($packageList as $packageName => $data) {
                if (! in_array($packageName, $exclude, true) && isset($data['install_path'])) {
                    $installPaths[] = $data['install_path'];
                }
            }
        }

        $newPaths = [];

        foreach ($namespacePaths as $namespace => $srcPaths) {
            $add = false;

            foreach ($srcPaths as $path) {
                foreach ($installPaths as $installPath) {
                    if ($installPath === substr($path, 0, strlen($installPath))) {
                        $add = true;
                        break 2;
                    }
                }
            }

            if ($add) {
                // Composer stores namespaces with trailing slash. We don't.
                $newPaths[rtrim($namespace, '\\ ')] = $srcPaths;
            }
        }

        $this->addNamespace($newPaths);
    }

    private function loadComposerClassmap(ClassLoader $composer): void
    {
        $classes = $composer->getClassMap();

        $this->classmap = array_merge($this->classmap, $classes);
    }

    /**
     * Locates autoload information from Composer, if available.
     *
     * @deprecated No longer used.
     */
    protected function discoverComposerNamespaces()
    {
        if (! is_file(COMPOSER_PATH)) {
            return;
        }

        /**
         * @var ClassLoader $composer
         */
        $composer = include COMPOSER_PATH;
        $paths    = $composer->getPrefixesPsr4();
        $classes  = $composer->getClassMap();

        unset($composer);

        // Get rid of CodeIgniter so we don't have duplicates
        if (isset($paths['CodeIgniter\\'])) {
            unset($paths['CodeIgniter\\']);
        }

        $newPaths = [];

        foreach ($paths as $key => $value) {
            // Composer stores namespaces with trailing slash. We don't.
            $newPaths[rtrim($key, '\\ ')] = $value;
        }

        $this->prefixes = array_merge($this->prefixes, $newPaths);
        $this->classmap = array_merge($this->classmap, $classes);
    }

    /**
     * Loads helpers
     */
    public function loadHelpers(): void
    {
        helper($this->helpers);
    }
}
