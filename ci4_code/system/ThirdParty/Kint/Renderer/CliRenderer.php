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

use Kint\Object\BasicObject;

class CliRenderer extends TextRenderer
{
    /**
     * @var bool enable colors when Kint is run in *UNIX* command line
     */
    public static $cli_colors = true;

    /**
     * Forces utf8 output on windows.
     *
     * @var bool
     */
    public static $force_utf8 = false;

    /**
     * Detects the terminal width on startup.
     *
     * @var bool
     */
    public static $detect_width = true;

    /**
     * The minimum width to detect terminal size as.
     *
     * Less than this is ignored and falls back to default width.
     *
     * @var int
     */
    public static $min_terminal_width = 40;

    protected static $terminal_width = null;

    protected $windows_output = false;

    protected $colors = false;

    public function __construct()
    {
        parent::__construct();

        if (!self::$force_utf8) {
            $this->windows_output = KINT_WIN;
        }

        if (!self::$terminal_width) {
            if (!KINT_WIN && self::$detect_width) {
                self::$terminal_width = \exec('tput cols');
            }

            if (self::$terminal_width < self::$min_terminal_width) {
                self::$terminal_width = self::$default_width;
            }
        }

        $this->colors = $this->windows_output ? false : self::$cli_colors;

        $this->header_width = self::$terminal_width;
    }

    public function colorValue($string)
    {
        if (!$this->colors) {
            return $string;
        }

        return "\x1b[32m".\str_replace("\n", "\x1b[0m\n\x1b[32m", $string)."\x1b[0m";
    }

    public function colorType($string)
    {
        if (!$this->colors) {
            return $string;
        }

        return "\x1b[35;1m".\str_replace("\n", "\x1b[0m\n\x1b[35;1m", $string)."\x1b[0m";
    }

    public function colorTitle($string)
    {
        if (!$this->colors) {
            return $string;
        }

        return "\x1b[36m".\str_replace("\n", "\x1b[0m\n\x1b[36m", $string)."\x1b[0m";
    }

    public function renderTitle(BasicObject $o)
    {
        if ($this->windows_output) {
            return $this->utf8ToWindows(parent::renderTitle($o));
        }

        return parent::renderTitle($o);
    }

    public function preRender()
    {
        return PHP_EOL;
    }

    public function postRender()
    {
        if ($this->windows_output) {
            return $this->utf8ToWindows(parent::postRender());
        }

        return parent::postRender();
    }

    public function escape($string, $encoding = false)
    {
        return \str_replace("\x1b", '\\x1b', $string);
    }

    protected function utf8ToWindows($string)
    {
        return \str_replace(
            array('┌', '═', '┐', '│', '└', '─', '┘'),
            array("\xda", "\xdc", "\xbf", "\xb3", "\xc0", "\xc4", "\xd9"),
            $string
        );
    }
}
