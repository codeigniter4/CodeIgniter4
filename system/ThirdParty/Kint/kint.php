<?php

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
use Kint\Object\BasicObject;
use Kint\Parser\Parser;
use Kint\Parser\Plugin;
use Kint\Renderer\Renderer;
use Kint\Renderer\TextRenderer;

class Kint
{
    const MODE_RICH = 'r';
    const MODE_TEXT = 't';
    const MODE_CLI = 'c';
    const MODE_PLAIN = 'p';

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
    public static $app_root_dirs = array();

    /**
     * @var int max array/object levels to go deep, if zero no limits are applied
     */
    public static $max_depth = 6;

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
    public static $aliases = array(
        array('Kint\\Kint', 'dump'),
        array('Kint\\Kint', 'trace'),
        array('Kint\\Kint', 'dumpArray'),
    );

    /**
     * @var array<mixed, string> Array of modes to renderer class names
     */
    public static $renderers = array(
        self::MODE_RICH => 'Kint\\Renderer\\RichRenderer',
        self::MODE_PLAIN => 'Kint\\Renderer\\PlainRenderer',
        self::MODE_TEXT => 'Kint\\Renderer\\TextRenderer',
        self::MODE_CLI => 'Kint\\Renderer\\CliRenderer',
    );

    public static $plugins = array(
        'Kint\\Parser\\ArrayObjectPlugin',
        'Kint\\Parser\\Base64Plugin',
        'Kint\\Parser\\BlacklistPlugin',
        'Kint\\Parser\\ClassMethodsPlugin',
        'Kint\\Parser\\ClassStaticsPlugin',
        'Kint\\Parser\\ClosurePlugin',
        'Kint\\Parser\\ColorPlugin',
        'Kint\\Parser\\DateTimePlugin',
        'Kint\\Parser\\FsPathPlugin',
        'Kint\\Parser\\IteratorPlugin',
        'Kint\\Parser\\JsonPlugin',
        'Kint\\Parser\\MicrotimePlugin',
        'Kint\\Parser\\SimpleXMLElementPlugin',
        'Kint\\Parser\\SplFileInfoPlugin',
        'Kint\\Parser\\SplObjectStoragePlugin',
        'Kint\\Parser\\StreamPlugin',
        'Kint\\Parser\\TablePlugin',
        'Kint\\Parser\\ThrowablePlugin',
        'Kint\\Parser\\TimestampPlugin',
        'Kint\\Parser\\TracePlugin',
        'Kint\\Parser\\XmlPlugin',
    );

    protected static $plugin_pool = array();

    protected $parser;
    protected $renderer;

    public function __construct(Parser $p, Renderer $r)
    {
        $this->parser = $p;
        $this->renderer = $r;
    }

    public function setParser(Parser $p)
    {
        $this->parser = $p;
    }

    public function getParser()
    {
        return $this->parser;
    }

    public function setRenderer(Renderer $r)
    {
        $this->renderer = $r;
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function setStatesFromStatics(array $statics)
    {
        $this->renderer->setStatics($statics);

        $this->parser->setDepthLimit(isset($statics['max_depth']) ? $statics['max_depth'] : false);
        $this->parser->clearPlugins();

        if (!isset($statics['plugins'])) {
            return;
        }

        $plugins = array();

        foreach ($statics['plugins'] as $plugin) {
            if ($plugin instanceof Plugin) {
                $plugins[] = $plugin;
            } elseif (\is_string($plugin) && \is_subclass_of($plugin, 'Kint\\Parser\\Plugin')) {
                if (!isset(self::$plugin_pool[$plugin])) {
                    $p = new $plugin();
                    self::$plugin_pool[$plugin] = $p;
                }
                $plugins[] = self::$plugin_pool[$plugin];
            }
        }

        $plugins = $this->renderer->filterParserPlugins($plugins);

        foreach ($plugins as $plugin) {
            $this->parser->addPlugin($plugin);
        }
    }

    public function setStatesFromCallInfo(array $info)
    {
        $this->renderer->setCallInfo($info);

        if (isset($info['modifiers']) && \is_array($info['modifiers']) && \in_array('+', $info['modifiers'], true)) {
            $this->parser->setDepthLimit(false);
        }

        $this->parser->setCallerClass(isset($info['caller']['class']) ? $info['caller']['class'] : null);
    }

    /**
     * Renders a list of vars including the pre and post renders.
     *
     * @param array         $vars Data to dump
     * @param BasicObject[] $base Base objects
     *
     * @return string
     */
    public function dumpAll(array $vars, array $base)
    {
        if (\array_keys($vars) !== \array_keys($base)) {
            throw new InvalidArgumentException('Kint::dumpAll requires arrays of identical size and keys as arguments');
        }

        $output = $this->renderer->preRender();

        if ($vars === array()) {
            $output .= $this->renderer->renderNothing();
        }

        foreach ($vars as $key => $arg) {
            if (!$base[$key] instanceof BasicObject) {
                throw new InvalidArgumentException('Kint::dumpAll requires all elements of the second argument to be BasicObject instances');
            }
            $output .= $this->dumpVar($arg, $base[$key]);
        }

        $output .= $this->renderer->postRender();

        return $output;
    }

    /**
     * Dumps and renders a var.
     *
     * @param mixed       $var  Data to dump
     * @param BasicObject $base Base object
     *
     * @return string
     */
    public function dumpVar(&$var, BasicObject $base)
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
    public static function getStatics()
    {
        return array(
            'aliases' => self::$aliases,
            'app_root_dirs' => self::$app_root_dirs,
            'cli_detection' => self::$cli_detection,
            'display_called_from' => self::$display_called_from,
            'enabled_mode' => self::$enabled_mode,
            'expanded' => self::$expanded,
            'file_link_format' => self::$file_link_format,
            'max_depth' => self::$max_depth,
            'mode_default' => self::$mode_default,
            'mode_default_cli' => self::$mode_default_cli,
            'plugins' => self::$plugins,
            'renderers' => self::$renderers,
            'return' => self::$return,
        );
    }

    /**
     * Creates a Kint instances based on static settings.
     *
     * Also calls setStatesFromStatics for you
     *
     * @param array $statics array of statics as returned by getStatics
     *
     * @return null|\Kint\Kint
     */
    public static function createFromStatics(array $statics)
    {
        $mode = false;

        if (isset($statics['enabled_mode'])) {
            $mode = $statics['enabled_mode'];

            if (true === $statics['enabled_mode'] && isset($statics['mode_default'])) {
                $mode = $statics['mode_default'];

                if (PHP_SAPI === 'cli' && !empty($statics['cli_detection']) && isset($statics['mode_default_cli'])) {
                    $mode = $statics['mode_default_cli'];
                }
            }
        }

        if (!$mode) {
            return null;
        }

        if (!isset($statics['renderers'][$mode])) {
            $renderer = new TextRenderer();
        } else {
            /** @var Renderer */
            $renderer = new $statics['renderers'][$mode]();
        }

        return new self(new Parser(), $renderer);
    }

    /**
     * Creates base objects given parameter info.
     *
     * @param array $params Parameters as returned from getCallInfo
     * @param int   $argc   Number of arguments the helper was called with
     *
     * @return BasicObject[] Base objects for the arguments
     */
    public static function getBasesFromParamInfo(array $params, $argc)
    {
        static $blacklist = array(
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
        );

        $params = \array_values($params);
        $bases = array();

        for ($i = 0; $i < $argc; ++$i) {
            if (isset($params[$i])) {
                $param = $params[$i];
            } else {
                $param = null;
            }

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

            $bases[] = BasicObject::blank($name, $access_path);
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
     * @param int     $argc    Number of arguments
     *
     * @return array{params:null|array, modifiers:array, callee:null|array, caller:null|array, trace:array[]} Call info
     */
    public static function getCallInfo(array $aliases, array $trace, $argc)
    {
        $found = false;
        $callee = null;
        $caller = null;
        $miniTrace = array();

        foreach ($trace as $index => $frame) {
            if (Utils::traceFrameIsListed($frame, $aliases)) {
                $found = true;
                $miniTrace = array();
            }

            if (!Utils::traceFrameIsListed($frame, array('spl_autoload_call'))) {
                $miniTrace[] = $frame;
            }
        }

        if ($found) {
            $callee = \reset($miniTrace) ?: null;

            /** @var null|array Psalm bug workaround */
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

        $call = self::getSingleCall($callee ?: array(), $argc);

        $ret = array(
            'params' => null,
            'modifiers' => array(),
            'callee' => $callee,
            'caller' => $caller,
            'trace' => $miniTrace,
        );

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
        if (!self::$enabled_mode) {
            return 0;
        }

        Utils::normalizeAliases(self::$aliases);

        $args = \func_get_args();

        $call_info = self::getCallInfo(self::$aliases, \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), \count($args));

        $statics = self::getStatics();

        if (\in_array('~', $call_info['modifiers'], true)) {
            $statics['enabled_mode'] = self::MODE_TEXT;
        }

        $kintstance = self::createFromStatics($statics);
        if (!$kintstance) {
            // Should never happen
            return 0; // @codeCoverageIgnore
        }

        if (\in_array('-', $call_info['modifiers'], true)) {
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        $kintstance->setStatesFromStatics($statics);
        $kintstance->setStatesFromCallInfo($call_info);

        $trimmed_trace = array();
        $trace = \debug_backtrace(true);

        foreach ($trace as $frame) {
            if (Utils::traceFrameIsListed($frame, self::$aliases)) {
                $trimmed_trace = array();
            }

            $trimmed_trace[] = $frame;
        }

        $output = $kintstance->dumpAll(
            array($trimmed_trace),
            array(BasicObject::blank('Kint\\Kint::trace()', 'debug_backtrace(true)'))
        );

        if (self::$return || \in_array('@', $call_info['modifiers'], true)) {
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
     * Functionally equivalent to Kint::dump(1) or Kint::dump(debug_backtrace(true))
     *
     * @return int|string
     */
    public static function dump()
    {
        if (!self::$enabled_mode) {
            return 0;
        }

        Utils::normalizeAliases(self::$aliases);

        $args = \func_get_args();

        $call_info = self::getCallInfo(self::$aliases, \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), \count($args));

        $statics = self::getStatics();

        if (\in_array('~', $call_info['modifiers'], true)) {
            $statics['enabled_mode'] = self::MODE_TEXT;
        }

        $kintstance = self::createFromStatics($statics);
        if (!$kintstance) {
            // Should never happen
            return 0; // @codeCoverageIgnore
        }

        if (\in_array('-', $call_info['modifiers'], true)) {
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        $kintstance->setStatesFromStatics($statics);
        $kintstance->setStatesFromCallInfo($call_info);

        // If the call is Kint::dump(1) then dump a backtrace instead
        if ($args === array(1) && (!isset($call_info['params'][0]['name']) || '1' === $call_info['params'][0]['name'])) {
            $args = \debug_backtrace(true);
            $trace = array();

            foreach ($args as $index => $frame) {
                if (Utils::traceFrameIsListed($frame, self::$aliases)) {
                    $trace = array();
                }

                $trace[] = $frame;
            }

            if (isset($call_info['callee']['function'])) {
                $tracename = $call_info['callee']['function'].'(1)';
                if (isset($call_info['callee']['class'], $call_info['callee']['type'])) {
                    $tracename = $call_info['callee']['class'].$call_info['callee']['type'].$tracename;
                }
            } else {
                $tracename = 'Kint\\Kint::dump(1)';
            }

            $tracebase = BasicObject::blank($tracename, 'debug_backtrace(true)');

            $output = $kintstance->dumpAll(array($trace), array($tracebase));
        } else {
            $bases = self::getBasesFromParamInfo(
                isset($call_info['params']) ? $call_info['params'] : array(),
                \count($args)
            );
            $output = $kintstance->dumpAll($args, $bases);
        }

        if (self::$return || \in_array('@', $call_info['modifiers'], true)) {
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
     *
     * @param string $file
     *
     * @return string
     */
    public static function shortenPath($file)
    {
        $file = \array_values(\array_filter(\explode('/', \str_replace('\\', '/', $file)), 'strlen'));

        $longest_match = 0;
        $match = '/';

        foreach (self::$app_root_dirs as $path => $alias) {
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
            $file = \array_merge(array($match), \array_slice($file, $longest_match));

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

    public static function getIdeLink($file, $line)
    {
        return \str_replace(array('%f', '%l'), array($file, $line), self::$file_link_format);
    }

    /**
     * Returns specific function call info from a stack trace frame, or null if no match could be found.
     *
     * @param array $frame The stack trace frame in question
     * @param int   $argc  The amount of arguments received
     *
     * @return null|array{parameters:array, modifiers:array} params and modifiers, or null if a specific call could not be determined
     */
    protected static function getSingleCall(array $frame, $argc)
    {
        if (!isset($frame['file'], $frame['line'], $frame['function']) || !\is_readable($frame['file'])) {
            return null;
        }

        if (empty($frame['class'])) {
            $callfunc = $frame['function'];
        } else {
            $callfunc = array($frame['class'], $frame['function']);
        }

        $calls = CallFinder::getFunctionCalls(
            \file_get_contents($frame['file']),
            $frame['line'],
            $callfunc
        );

        $return = null;

        foreach ($calls as $call) {
            $is_unpack = false;

            // Handle argument unpacking as a last resort
            if (KINT_PHP56) {
                foreach ($call['parameters'] as $i => &$param) {
                    if (0 === \strpos($param['name'], '...')) {
                        if ($i < $argc && $i === \count($call['parameters']) - 1) {
                            for ($j = 1; $j + $i < $argc; ++$j) {
                                $call['parameters'][] = array(
                                    'name' => 'array_values('.\substr($param['name'], 3).')['.$j.']',
                                    'path' => 'array_values('.\substr($param['path'], 3).')['.$j.']',
                                    'expression' => false,
                                );
                            }

                            $param['name'] = 'reset('.\substr($param['name'], 3).')';
                            $param['path'] = 'reset('.\substr($param['path'], 3).')';
                            $param['expression'] = false;
                        } else {
                            $call['parameters'] = \array_slice($call['parameters'], 0, $i);
                        }

                        $is_unpack = true;
                        break;
                    }

                    if ($i >= $argc) {
                        continue 2;
                    }
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
