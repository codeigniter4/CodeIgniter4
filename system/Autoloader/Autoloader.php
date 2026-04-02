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

namespace CodeIgniter\Autoloader;

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\RuntimeException;
use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Config\Autoload;
use Config\Kint as KintConfig;
use Config\Modules;
use Kint\Kint;
use Kint\Renderer\CliRenderer;
use Kint\Renderer\RichRenderer;

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
 *
 * @see \CodeIgniter\Autoloader\AutoloaderTest
 */
class Autoloader
{
    /**
     * Stores namespaces as key, and path as values.
     *
     * @var array<non-empty-string, list<non-empty-string>>
     */
    protected $prefixes = [];

    /**
     * Stores class name as key, and path as values.
     *
     * @var array<class-string, non-empty-string>
     */
    protected $classmap = [];

    /**
     * Stores files as a list.
     *
     * @var list<non-empty-string>
     */
    protected $files = [];

    /**
     * Stores helper list.
     * Always load the URL helper, it should be used in most apps.
     *
     * @var list<non-empty-string>
     */
    protected $helpers = ['url'];

    public function __construct(private readonly string $composerPath = COMPOSER_PATH)
    {
    }

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

        if ($config->helpers !== []) {
            $this->helpers = [...$this->helpers, ...$config->helpers];
        }

        if (is_file($this->composerPath)) {
            $this->loadComposerAutoloader($modules);
        }

        return $this;
    }

    private function loadComposerAutoloader(Modules $modules): void
    {
        // The path to the vendor directory.
        // We do not want to enforce this, so set the constant if Composer was used.
        if (! defined('VENDORPATH')) {
            define('VENDORPATH', dirname($this->composerPath) . DIRECTORY_SEPARATOR);
        }

        /** @var ClassLoader $composer */
        $composer = include $this->composerPath;

        // Should we load through Composer's namespaces, also?
        if ($modules->discoverInComposer) {
            $composerPackages = $modules->composerPackages;
            $this->loadComposerNamespaces($composer, $composerPackages ?? []);
        }

        unset($composer);
    }

    /**
     * Register the loader with the SPL autoloader stack
     * in the following order:
     *
     * 1. Classmap loader
     * 2. PSR-4 autoloader
     * 3. Non-class files
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register($this->loadClassmap(...), true);
        spl_autoload_register($this->loadClass(...), true);

        foreach ($this->files as $file) {
            $this->includeFile($file);
        }
    }

    /**
     * Unregisters the autoloader from the SPL autoload stack.
     */
    public function unregister(): void
    {
        spl_autoload_unregister($this->loadClass(...));
        spl_autoload_unregister($this->loadClassmap(...));
    }

    /**
     * Registers namespaces with the autoloader.
     *
     * @param array<non-empty-string, list<non-empty-string>|non-empty-string>|non-empty-string $namespace
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
     * @return ($prefix is null ? array<non-empty-string, list<non-empty-string>> : list<non-empty-string>)
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
     * @param class-string $class The fully qualified class name.
     *
     * @internal For `spl_autoload_register` use.
     */
    public function loadClassmap(string $class): void
    {
        $file = $this->classmap[$class] ?? '';

        if (is_string($file) && $file !== '') {
            $this->includeFile($file);
        }
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param class-string $class The fully qualified class name.
     *
     * @internal For `spl_autoload_register` use.
     */
    public function loadClass(string $class): void
    {
        $this->loadInNamespace($class);
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param class-string $class The fully qualified class name.
     *
     * @return false|non-empty-string The mapped file name on success, or boolean false on fail
     */
    protected function loadInNamespace(string $class)
    {
        if (! str_contains($class, '\\')) {
            return false;
        }

        foreach ($this->prefixes as $namespace => $directories) {
            if (str_starts_with($class, $namespace)) {
                $relativeClassPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace)));

                foreach ($directories as $directory) {
                    $directory = rtrim($directory, '\\/');

                    $filePath = $directory . $relativeClassPath . '.php';
                    $filename = $this->includeFile($filePath);

                    if ($filename !== false) {
                        return $filename;
                    }
                }
            }
        }

        return false;
    }

    /**
     * A central way to include a file. Split out primarily for testing purposes.
     *
     * @return false|non-empty-string The filename on success, false if the file is not loaded
     */
    protected function includeFile(string $file)
    {
        if (is_file($file)) {
            include_once $file;

            return $file;
        }

        return false;
    }

    /**
     * @param array{only?: list<string>, exclude?: list<string>} $composerPackages
     */
    private function loadComposerNamespaces(ClassLoader $composer, array $composerPackages): void
    {
        $namespacePaths = $composer->getPrefixesPsr4();

        // Get rid of duplicated namespaces.
        $duplicatedNamespaces = ['CodeIgniter', APP_NAMESPACE, 'Config'];

        foreach ($duplicatedNamespaces as $ns) {
            if (isset($namespacePaths[$ns . '\\'])) {
                unset($namespacePaths[$ns . '\\']);
            }
        }

        if (! method_exists(InstalledVersions::class, 'getAllRawData')) { // @phpstan-ignore function.alreadyNarrowedType
            throw new RuntimeException(
                'Your Composer version is too old.'
                . ' Please update Composer (run `composer self-update`) to v2.0.14 or later'
                . ' and remove your vendor/ directory, and run `composer update`.',
            );
        }
        // This method requires Composer 2.0.14 or later.
        $allData     = InstalledVersions::getAllRawData();
        $packageList = [];

        foreach ($allData as $list) {
            $packageList = array_merge($packageList, $list['versions']);
        }

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
                    if (str_starts_with($path, $installPath)) {
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

    /**
     * Loads helpers
     */
    public function loadHelpers(): void
    {
        helper($this->helpers);
    }

    /**
     * Initializes Kint
     */
    public function initializeKint(bool $debug = false): void
    {
        if ($debug) {
            $this->autoloadKint();
            $this->configureKint();
        } elseif (class_exists(Kint::class)) {
            // In case that Kint is already loaded via Composer.
            Kint::$enabled_mode = false;
        }

        helper('kint');
    }

    private function autoloadKint(): void
    {
        // If we have KINT_DIR it means it's already loaded via composer
        if (! defined('KINT_DIR')) {
            spl_autoload_register(function ($class): void {
                $class = explode('\\', $class);

                if (array_shift($class) !== 'Kint') {
                    return;
                }

                $file = SYSTEMPATH . 'ThirdParty/Kint/' . implode('/', $class) . '.php';

                if (is_file($file)) {
                    require_once $file;
                }
            });

            require_once SYSTEMPATH . 'ThirdParty/Kint/init.php';
        }
    }

    private function configureKint(): void
    {
        $config = new KintConfig();

        Kint::$depth_limit         = $config->maxDepth;
        Kint::$display_called_from = $config->displayCalledFrom;
        Kint::$expanded            = $config->expanded;

        if (isset($config->plugins) && is_array($config->plugins)) {
            Kint::$plugins = $config->plugins;
        }

        $csp = service('csp');
        if ($csp->scriptNonceEnabled()) {
            RichRenderer::$js_nonce = $csp->getScriptNonce();
        }

        if ($csp->styleNonceEnabled()) {
            RichRenderer::$css_nonce = $csp->getStyleNonce();
        }

        RichRenderer::$theme  = $config->richTheme;
        RichRenderer::$folder = $config->richFolder;

        if (isset($config->richObjectPlugins) && is_array($config->richObjectPlugins)) {
            RichRenderer::$value_plugins = $config->richObjectPlugins;
        }
        if (isset($config->richTabPlugins) && is_array($config->richTabPlugins)) {
            RichRenderer::$tab_plugins = $config->richTabPlugins;
        }

        CliRenderer::$cli_colors         = $config->cliColors;
        CliRenderer::$force_utf8         = $config->cliForceUTF8;
        CliRenderer::$detect_width       = $config->cliDetectWidth;
        CliRenderer::$min_terminal_width = $config->cliMinWidth;
    }
}
