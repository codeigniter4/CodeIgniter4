<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint;

use InvalidArgumentException;
use Kint\Parser\ConstructablePluginInterface;
use Kint\Parser\Parser;
use Kint\Parser\PluginInterface;
use Kint\Renderer\RendererInterface;
use Kint\Renderer\TextRenderer;
use Kint\Zval\Value;

/**
 * @psalm-consistent-constructor
 */
class Kint implements FacadeInterface
{
    public const MODE_RICH = 'r';
    public const MODE_TEXT = 't';
    public const MODE_CLI = 'c';
    public const MODE_PLAIN = 'p';

    /**
     * @var mixed Kint mode
     *
     * false: Disabled
     * true: Enabled, default mode selection
     * other: Manual mode selection
     */
    public static $enabled_mode = true;

    /**
     * Default mode.
     *
     * @var string
     */
    public static $mode_default = self::MODE_RICH;

    /**
     * Default mode in CLI with cli_detection on.
     *
     * @var string
     */
    public static $mode_default_cli = self::MODE_CLI;

    /**
     * @var bool Return output instead of echoing
     */
    public static $return;

    /**
     * @var string format of the link to the source file in trace entries.
     *
     * Use %f for file path, %l for line number.
     *
     * [!] EXAMPLE (works with for phpStorm and RemoteCall Plugin):
     *
     * Kint::$file_link_format = 'http://localhost:8091/?message=%f:%l';
     */
    public static $file_link_format = '';

    /**
     * @var bool whether to display where kint was called from
     */
    public static $display_called_from = true;

    /**
     * @var array base directories of your application that will be displayed instead of the full path.
     *
     * Keys are paths, values are replacement strings
     *
     * [!] EXAMPLE (for Laravel 5):
     *
     * Kint::$app_root_dirs = [
     *     base_path() => '<BASE>',
     *     app_path() => '<APP>',
     *     config_path() => '<CONFIG>',
     *     database_path() => '<DATABASE>',
     *     public_path() => '<PUBLIC>',
     *     resource_path() => '<RESOURCE>',
     *     storage_path() => '<STORAGE>',
     * ];
     *
     * Defaults to [$_SERVER['DOCUMENT_ROOT'] => '<ROOT>']
     */
    public static $app_root_dirs = [];

    /**
     * @var int depth limit for array/object traversal. 0 for no limit
     */
    public static $depth_limit = 7;

    /**
     * @var bool expand all trees by default for rich view
     */
    public static $expanded = false;

    /**
     * @var bool enable detection when Kint is command line.
     *
     * Formats output with whitespace only; does not HTML-escape it
     */
    public static $cli_detection = true;

    /**
     * @var array Kint aliases. Add debug functions in Kint wrappers here to fix modifiers and backtraces
     */
    public static $aliases = [
        ['Kint\\Kint', 'dump'],
        ['Kint\\Kint', 'trace'],
        ['Kint\\Kint', 'dumpArray'],
    ];

    /**
     * @psalm-var class-string[] Array of modes to renderer class names
     */
    public static $renderers = [
        self::MODE_RICH => \Kint\Renderer\RichRenderer::class,
        self::MODE_PLAIN => \Kint\Renderer\PlainRenderer::class,
        self::MODE_TEXT => \Kint\Renderer\TextRenderer::class,
        self::MODE_CLI => \Kint\Renderer\CliRenderer::class,
    ];

    /**
     * @psalm-var class-string[]
     */
    public static $plugins = [
        \Kint\Parser\ArrayLimitPlugin::class,
        \Kint\Parser\ArrayObjectPlugin::class,
        \Kint\Parser\Base64Plugin::class,
        \Kint\Parser\BlacklistPlugin::class,
        \Kint\Parser\ClassMethodsPlugin::class,
        \Kint\Parser\ClassStaticsPlugin::class,
        \Kint\Parser\ClosurePlugin::class,
        \Kint\Parser\ColorPlugin::class,
        \Kint\Parser\DateTimePlugin::class,
        \Kint\Parser\EnumPlugin::class,
        \Kint\Parser\FsPathPlugin::class,
        \Kint\Parser\IteratorPlugin::class,
        \Kint\Parser\JsonPlugin::class,
        \Kint\Parser\MicrotimePlugin::class,
        \Kint\Parser\SimpleXMLElementPlugin::class,
        \Kint\Parser\SplFileInfoPlugin::class,
        \Kint\Parser\SplObjectStoragePlugin::class,
        \Kint\Parser\StreamPlugin::class,
        \Kint\Parser\TablePlugin::class,
        \Kint\Parser\ThrowablePlugin::class,
        \Kint\Parser\TimestampPlugin::class,
        \Kint\Parser\TracePlugin::class,
        \Kint\Parser\XmlPlugin::class,
    ];

    protected static $plugin_pool = [];

    protected $parser;
    protected $renderer;

    public function __construct(Parser $p, RendererInterface $r)
    {
        $this->parser = $p;
        $this->renderer = $r;
    }

    public function setParser(Parser $p): void
    {
        $this->parser = $p;
    }

    public function getParser(): Parser
    {
        return $this->parser;
    }

    public function setRenderer(RendererInterface $r): void
    {
        $this->renderer = $r;
    }

    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }

    public function setStatesFromStatics(array $statics): void
    {
        $this->renderer->setStatics($statics);

        $this->parser->setDepthLimit(isset($statics['depth_limit']) ? $statics['depth_limit'] : 0);
        $this->parser->clearPlugins();

        if (!isset($statics['plugins'])) {
            return;
        }

        $plugins = [];

        foreach ($statics['plugins'] as $plugin) {
            if ($plugin instanceof PluginInterface) {
                $plugins[] = $plugin;
            } elseif (\is_string($plugin) && \is_subclass_of($plugin, ConstructablePluginInterface::class)) {
                if (!isset(static::$plugin_pool[$plugin])) {
                    $p = new $plugin();
                    static::$plugin_pool[$plugin] = $p;
                }
                $plugins[] = static::$plugin_pool[$plugin];
            }
        }

        $plugins = $this->renderer->filterParserPlugins($plugins);

        foreach ($plugins as $plugin) {
            $this->parser->addPlugin($plugin);
        }
    }

    public function setStatesFromCallInfo(array $info): void
    {
        $this->renderer->setCallInfo($info);

        if (isset($info['modifiers']) && \is_array($info['modifiers']) && \in_array('+', $info['modifiers'], true)) {
            $this->parser->setDepthLimit(0);
        }

        $this->parser->setCallerClass(isset($info['caller']['class']) ? $info['caller']['class'] : null);
    }

    public function dumpAll(array $vars, array $base): string
    {
        if (\array_keys($vars) !== \array_keys($base)) {
            throw new InvalidArgumentException('Kint::dumpAll requires arrays of identical size and keys as arguments');
        }

        $output = $this->renderer->preRender();

        if ([] === $vars) {
            $output .= $this->renderer->renderNothing();
        }

        foreach ($vars as $key => $arg) {
            if (!$base[$key] instanceof Value) {
                throw new InvalidArgumentException('Kint::dumpAll requires all elements of the second argument to be Value instances');
            }
            $output .= $this->dumpVar($arg, $base[$key]);
        }

        $output .= $this->renderer->postRender();

        return $output;
    }

    /**
     * Dumps and renders a var.
     *
     * @param mixed &$var Data to dump
     * @param Value $base Base object
     */
    protected function dumpVar(&$var, Value $base): string
    {
        return $this->renderer->render(
            $this->parser->parse($var, $base)
        );
    }

    /**
     * Gets all static settings at once.
     *
     * @return array Current static settings
     */
    public static function getStatics(): array
    {
        return [
            'aliases' => static::$aliases,
            'app_root_dirs' => static::$app_root_dirs,
            'cli_detection' => static::$cli_detection,
            'depth_limit' => static::$depth_limit,
            'display_called_from' => static::$display_called_from,
            'enabled_mode' => static::$enabled_mode,
            'expanded' => static::$expanded,
            'file_link_format' => static::$file_link_format,
            'mode_default' => static::$mode_default,
            'mode_default_cli' => static::$mode_default_cli,
            'plugins' => static::$plugins,
            'renderers' => static::$renderers,
            'return' => static::$return,
        ];
    }

    /**
     * Creates a Kint instance based on static settings.
     *
     * @param array $statics array of statics as returned by getStatics
     */
    public static function createFromStatics(array $statics): ?FacadeInterface
    {
        $mode = false;

        if (isset($statics['enabled_mode'])) {
            $mode = $statics['enabled_mode'];

            if (true === $mode && isset($statics['mode_default'])) {
                $mode = $statics['mode_default'];

                if (PHP_SAPI === 'cli' && !empty($statics['cli_detection']) && isset($statics['mode_default_cli'])) {
                    $mode = $statics['mode_default_cli'];
                }
            }
        }

        if (false === $mode) {
            return null;
        }

        /** @psalm-var class-string[] $statics['renderers'] */
        if (isset($statics['renderers'][$mode]) && \is_subclass_of($statics['renderers'][$mode], RendererInterface::class)) {
            $renderer = new $statics['renderers'][$mode]();
        } else {
            $renderer = new TextRenderer();
        }

        return new static(new Parser(), $renderer);
    }

    /**
     * Creates base objects given parameter info.
     *
     * @param array $params Parameters as returned from getCallInfo
     * @param int   $argc   Number of arguments the helper was called with
     *
     * @return Value[] Base objects for the arguments
     */
    public static function getBasesFromParamInfo(array $params, int $argc): array
    {
        static $blacklist = [
            'null',
            'true',
            'false',
            'array(...)',
            'array()',
            '[...]',
            '[]',
            '(...)',
            '()',
            '"..."',
            'b"..."',
            "'...'",
            "b'...'",
        ];

        $params = \array_values($params);
        $bases = [];

        for ($i = 0; $i < $argc; ++$i) {
            $param = $params[$i] ?? null;

            if (!isset($param['name']) || \is_numeric($param['name'])) {
                $name = null;
            } elseif (\in_array(\strtolower($param['name']), $blacklist, true)) {
                $name = null;
            } else {
                $name = $param['name'];
            }

            if (isset($param['path'])) {
                $access_path = $param['path'];

                if (!empty($param['expression'])) {
                    $access_path = '('.$access_path.')';
                }
            } else {
                $access_path = '$'.$i;
            }

            $bases[] = Value::blank($name, $access_path);
        }

        return $bases;
    }

    /**
     * Gets call info from the backtrace, alias, and argument count.
     *
     * Aliases must be normalized beforehand (Utils::normalizeAliases)
     *
     * @param array   $aliases Call aliases as found in Kint::$aliases
     * @param array[] $trace   Backtrace
     * @param array   $args    Arguments
     *
     * @return array Call info
     */
    public static function getCallInfo(array $aliases, array $trace, array $args): array
    {
        $found = false;
        $callee = null;
        $caller = null;
        $miniTrace = [];

        foreach ($trace as $index => $frame) {
            if (Utils::traceFrameIsListed($frame, $aliases)) {
                $found = true;
                $miniTrace = [];
            }

            if (!Utils::traceFrameIsListed($frame, ['spl_autoload_call'])) {
                $miniTrace[] = $frame;
            }
        }

        if ($found) {
            $callee = \reset($miniTrace) ?: null;
            $caller = \next($miniTrace) ?: null;
        }

        foreach ($miniTrace as $index => $frame) {
            if ((0 === $index && $callee === $frame) || isset($frame['file'], $frame['line'])) {
                unset($frame['object'], $frame['args']);
                $miniTrace[$index] = $frame;
            } else {
                unset($miniTrace[$index]);
            }
        }

        $miniTrace = \array_values($miniTrace);

        $call = static::getSingleCall($callee ?: [], $args);

        $ret = [
            'params' => null,
            'modifiers' => [],
            'callee' => $callee,
            'caller' => $caller,
            'trace' => $miniTrace,
        ];

        if ($call) {
            $ret['params'] = $call['parameters'];
            $ret['modifiers'] = $call['modifiers'];
        }

        return $ret;
    }

    /**
     * Dumps a backtrace.
     *
     * Functionally equivalent to Kint::dump(1) or Kint::dump(debug_backtrace(true))
     *
     * @return int|string
     */
    public static function trace()
    {
        if (false === static::$enabled_mode) {
            return 0;
        }

        Utils::normalizeAliases(static::$aliases);

        $call_info = static::getCallInfo(static::$aliases, \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), []);

        $statics = static::getStatics();

        if (\in_array('~', $call_info['modifiers'], true)) {
            $statics['enabled_mode'] = static::MODE_TEXT;
        }

        $kintstance = static::createFromStatics($statics);
        if (!$kintstance) {
            return 0;
        }

        if (\in_array('-', $call_info['modifiers'], true)) {
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        $kintstance->setStatesFromStatics($statics);
        $kintstance->setStatesFromCallInfo($call_info);

        $trimmed_trace = [];
        $trace = \debug_backtrace();

        foreach ($trace as $frame) {
            if (Utils::traceFrameIsListed($frame, static::$aliases)) {
                $trimmed_trace = [];
            }

            $trimmed_trace[] = $frame;
        }

        \array_shift($trimmed_trace);

        $output = $kintstance->dumpAll(
            [$trimmed_trace],
            [Value::blank('Kint\\Kint::trace()', 'debug_backtrace()')]
        );

        if (static::$return || \in_array('@', $call_info['modifiers'], true)) {
            return $output;
        }

        echo $output;

        if (\in_array('-', $call_info['modifiers'], true)) {
            \flush(); // @codeCoverageIgnore
        }

        return 0;
    }

    /**
     * Dumps some data.
     *
     * Functionally equivalent to Kint::dump(1) or Kint::dump(debug_backtrace())
     *
     * @param mixed ...$args
     *
     * @return int|string
     */
    public static function dump(...$args)
    {
        if (false === static::$enabled_mode) {
            return 0;
        }

        Utils::normalizeAliases(static::$aliases);

        $call_info = static::getCallInfo(static::$aliases, \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), $args);

        $statics = static::getStatics();

        if (\in_array('~', $call_info['modifiers'], true)) {
            $statics['enabled_mode'] = static::MODE_TEXT;
        }

        $kintstance = static::createFromStatics($statics);
        if (!$kintstance) {
            return 0;
        }

        if (\in_array('-', $call_info['modifiers'], true)) {
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        $kintstance->setStatesFromStatics($statics);
        $kintstance->setStatesFromCallInfo($call_info);

        $bases = static::getBasesFromParamInfo($call_info['params'] ?? [], \count($args));
        $output = $kintstance->dumpAll(\array_values($args), $bases);

        if (static::$return || \in_array('@', $call_info['modifiers'], true)) {
            return $output;
        }

        echo $output;

        if (\in_array('-', $call_info['modifiers'], true)) {
            \flush(); // @codeCoverageIgnore
        }

        return 0;
    }

    /**
     * generic path display callback, can be configured in app_root_dirs; purpose is
     * to show relevant path info and hide as much of the path as possible.
     */
    public static function shortenPath(string $file): string
    {
        $file = \array_values(\array_filter(\explode('/', \str_replace('\\', '/', $file)), 'strlen'));

        $longest_match = 0;
        $match = '/';

        foreach (static::$app_root_dirs as $path => $alias) {
            if (empty($path)) {
                continue;
            }

            $path = \array_values(\array_filter(\explode('/', \str_replace('\\', '/', $path)), 'strlen'));

            if (\array_slice($file, 0, \count($path)) === $path && \count($path) > $longest_match) {
                $longest_match = \count($path);
                $match = $alias;
            }
        }

        if ($longest_match) {
            $file = \array_merge([$match], \array_slice($file, $longest_match));

            return \implode('/', $file);
        }

        // fallback to find common path with Kint dir
        $kint = \array_values(\array_filter(\explode('/', \str_replace('\\', '/', KINT_DIR)), 'strlen'));

        foreach ($file as $i => $part) {
            if (!isset($kint[$i]) || $kint[$i] !== $part) {
                return ($i ? '.../' : '/').\implode('/', \array_slice($file, $i));
            }
        }

        return '/'.\implode('/', $file);
    }

    public static function getIdeLink(string $file, int $line): string
    {
        return \str_replace(['%f', '%l'], [$file, $line], static::$file_link_format);
    }

    /**
     * Returns specific function call info from a stack trace frame, or null if no match could be found.
     *
     * @param array $frame The stack trace frame in question
     * @param array $args  The arguments
     *
     * @return ?array params and modifiers, or null if a specific call could not be determined
     */
    protected static function getSingleCall(array $frame, array $args): ?array
    {
        if (
            !isset($frame['file'], $frame['line'], $frame['function']) ||
            !\is_readable($frame['file']) ||
            !$source = \file_get_contents($frame['file'])
        ) {
            return null;
        }

        if (empty($frame['class'])) {
            $callfunc = $frame['function'];
        } else {
            $callfunc = [$frame['class'], $frame['function']];
        }

        $calls = CallFinder::getFunctionCalls($source, $frame['line'], $callfunc);

        $argc = \count($args);

        $return = null;

        foreach ($calls as $call) {
            $is_unpack = false;

            // Handle argument unpacking as a last resort
            foreach ($call['parameters'] as $i => &$param) {
                if (0 === \strpos($param['name'], '...')) {
                    $is_unpack = true;

                    // If we're on the last param
                    if ($i < $argc && $i === \count($call['parameters']) - 1) {
                        unset($call['parameters'][$i]);

                        if (Utils::isAssoc($args)) {
                            // Associated unpacked arrays can be accessed by key
                            $keys = \array_slice(\array_keys($args), $i);

                            foreach ($keys as $key) {
                                $call['parameters'][] = [
                                    'name' => \substr($param['name'], 3).'['.\var_export($key, true).']',
                                    'path' => \substr($param['path'], 3).'['.\var_export($key, true).']',
                                    'expression' => false,
                                ];
                            }
                        } else {
                            // Numeric unpacked arrays have their order blown away like a pass
                            // through array_values so we can't access them directly at all
                            for ($j = 0; $j + $i < $argc; ++$j) {
                                $call['parameters'][] = [
                                    'name' => 'array_values('.\substr($param['name'], 3).')['.$j.']',
                                    'path' => 'array_values('.\substr($param['path'], 3).')['.$j.']',
                                    'expression' => false,
                                ];
                            }
                        }

                        $call['parameters'] = \array_values($call['parameters']);
                    } else {
                        $call['parameters'] = \array_slice($call['parameters'], 0, $i);
                    }

                    break;
                }

                if ($i >= $argc) {
                    continue 2;
                }
            }

            if ($is_unpack || \count($call['parameters']) === $argc) {
                if (null === $return) {
                    $return = $call;
                } else {
                    // If we have multiple calls on the same line with the same amount of arguments,
                    // we can't be sure which it is so just return null and let them figure it out
                    return null;
                }
            }
        }

        return $return;
    }
}
