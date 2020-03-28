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

namespace Kint\Renderer;

use Kint\Kint;
use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;
use Kint\Utils;

class TextRenderer extends Renderer
{
    /**
     * TextRenderer plugins should be instances of Kint\Renderer\Text\Plugin.
     */
    public static $plugins = array(
        'blacklist' => 'Kint\\Renderer\\Text\\BlacklistPlugin',
        'depth_limit' => 'Kint\\Renderer\\Text\\DepthLimitPlugin',
        'microtime' => 'Kint\\Renderer\\Text\\MicrotimePlugin',
        'recursion' => 'Kint\\Renderer\\Text\\RecursionPlugin',
        'trace' => 'Kint\\Renderer\\Text\\TracePlugin',
    );

    /**
     * Parser plugins must be instanceof one of these or
     * it will be removed for performance reasons.
     */
    public static $parser_plugin_whitelist = array(
        'Kint\\Parser\\BlacklistPlugin',
        'Kint\\Parser\\MicrotimePlugin',
        'Kint\\Parser\\StreamPlugin',
        'Kint\\Parser\\TracePlugin',
    );

    /**
     * The maximum length of a string before it is truncated.
     *
     * Falsey to disable
     *
     * @var int
     */
    public static $strlen_max = 0;

    /**
     * The default width of the terminal for headers.
     *
     * @var int
     */
    public static $default_width = 80;

    /**
     * Indentation width.
     *
     * @var int
     */
    public static $default_indent = 4;

    /**
     * Decorate the header and footer.
     *
     * @var bool
     */
    public static $decorations = true;

    /**
     * Sort mode for object properties.
     *
     * @var int
     */
    public static $sort = self::SORT_NONE;

    public $header_width = 80;
    public $indent_width = 4;

    protected $plugin_objs = array();

    public function __construct()
    {
        $this->header_width = self::$default_width;
        $this->indent_width = self::$default_indent;
    }

    public function render(BasicObject $o)
    {
        if ($plugin = $this->getPlugin(self::$plugins, $o->hints)) {
            if (\strlen($output = $plugin->render($o))) {
                return $output;
            }
        }

        $out = '';

        if (0 == $o->depth) {
            $out .= $this->colorTitle($this->renderTitle($o)).PHP_EOL;
        }

        $out .= $this->renderHeader($o);
        $out .= $this->renderChildren($o).PHP_EOL;

        return $out;
    }

    public function renderNothing()
    {
        if (self::$decorations) {
            return $this->colorTitle(
                $this->boxText('No argument', $this->header_width)
            ).PHP_EOL;
        }

        return $this->colorTitle('No argument').PHP_EOL;
    }

    public function boxText($text, $width)
    {
        $out = '┌'.\str_repeat('─', $width - 2).'┐'.PHP_EOL;

        if (\strlen($text)) {
            $text = Utils::truncateString($text, $width - 4);
            $text = \str_pad($text, $width - 4);

            $out .= '│ '.$this->escape($text).' │'.PHP_EOL;
        }

        $out .= '└'.\str_repeat('─', $width - 2).'┘';

        return $out;
    }

    public function renderTitle(BasicObject $o)
    {
        $name = (string) $o->getName();

        if (self::$decorations) {
            return $this->boxText($name, $this->header_width);
        }

        return Utils::truncateString($name, $this->header_width);
    }

    public function renderHeader(BasicObject $o)
    {
        $output = array();

        if ($o->depth) {
            if (null !== ($s = $o->getModifiers())) {
                $output[] = $s;
            }

            if (null !== $o->name) {
                $output[] = $this->escape(\var_export($o->name, true));

                if (null !== ($s = $o->getOperator())) {
                    $output[] = $this->escape($s);
                }
            }
        }

        if (null !== ($s = $o->getType())) {
            if ($o->reference) {
                $s = '&'.$s;
            }

            $output[] = $this->colorType($this->escape($s));
        }

        if (null !== ($s = $o->getSize())) {
            $output[] = '('.$this->escape($s).')';
        }

        if (null !== ($s = $o->getValueShort())) {
            if (self::$strlen_max) {
                $s = Utils::truncateString($s, self::$strlen_max);
            }
            $output[] = $this->colorValue($this->escape($s));
        }

        return \str_repeat(' ', $o->depth * $this->indent_width).\implode(' ', $output);
    }

    public function renderChildren(BasicObject $o)
    {
        if ('array' === $o->type) {
            $output = ' [';
        } elseif ('object' === $o->type) {
            $output = ' (';
        } else {
            return '';
        }

        $children = '';

        if ($o->value && \is_array($o->value->contents)) {
            if ($o instanceof InstanceObject && 'properties' === $o->value->getName()) {
                foreach (self::sortProperties($o->value->contents, self::$sort) as $obj) {
                    $children .= $this->render($obj);
                }
            } else {
                foreach ($o->value->contents as $child) {
                    $children .= $this->render($child);
                }
            }
        }

        if ($children) {
            $output .= PHP_EOL.$children;
            $output .= \str_repeat(' ', $o->depth * $this->indent_width);
        }

        if ('array' === $o->type) {
            $output .= ']';
        } else {
            $output .= ')';
        }

        return $output;
    }

    public function colorValue($string)
    {
        return $string;
    }

    public function colorType($string)
    {
        return $string;
    }

    public function colorTitle($string)
    {
        return $string;
    }

    public function postRender()
    {
        if (self::$decorations) {
            $output = \str_repeat('═', $this->header_width);
        } else {
            $output = '';
        }

        if (!$this->show_trace) {
            return $this->colorTitle($output);
        }

        if ($output) {
            $output .= PHP_EOL;
        }

        return $this->colorTitle($output.$this->calledFrom().PHP_EOL);
    }

    public function filterParserPlugins(array $plugins)
    {
        $return = array();

        foreach ($plugins as $index => $plugin) {
            foreach (self::$parser_plugin_whitelist as $whitelist) {
                if ($plugin instanceof $whitelist) {
                    $return[] = $plugin;
                    continue 2;
                }
            }
        }

        return $return;
    }

    public function ideLink($file, $line)
    {
        return $this->escape(Kint::shortenPath($file)).':'.$line;
    }

    public function escape($string, $encoding = false)
    {
        return $string;
    }

    protected function calledFrom()
    {
        $output = '';

        if (isset($this->call_info['callee']['file'])) {
            $output .= 'Called from '.$this->ideLink(
                $this->call_info['callee']['file'],
                $this->call_info['callee']['line']
            );
        }

        if (isset($this->call_info['callee']['function']) && (
                !empty($this->call_info['callee']['class']) ||
                !\in_array(
                    $this->call_info['callee']['function'],
                    array('include', 'include_once', 'require', 'require_once'),
                    true
                )
            )
        ) {
            $output .= ' [';
            if (isset($this->call_info['callee']['class'])) {
                $output .= $this->call_info['callee']['class'];
            }
            if (isset($this->call_info['callee']['type'])) {
                $output .= $this->call_info['callee']['type'];
            }
            $output .= $this->call_info['callee']['function'].'()]';
        }

        return $output;
    }

    protected function getPlugin(array $plugins, array $hints)
    {
        if ($plugins = $this->matchPlugins($plugins, $hints)) {
            $plugin = \end($plugins);

            if (!isset($this->plugin_objs[$plugin])) {
                $this->plugin_objs[$plugin] = new $plugin($this);
            }

            return $this->plugin_objs[$plugin];
        }
    }
}
