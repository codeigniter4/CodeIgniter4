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

use Exception;
use Kint\Zval\Value;
use Throwable;

class CliRenderer extends TextRenderer
{
    /**
     * @var bool enable colors
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

    /**
     * Which stream to check for VT100 support on windows.
     *
     * null uses STDOUT if it's defined
     *
     * @var null|resource
     */
    public static $windows_stream = null;

    protected static $terminal_width = null;

    protected $windows_output = false;

    protected $colors = false;

    public function __construct()
    {
        parent::__construct();

        if (!self::$force_utf8 && KINT_WIN) {
            if (!KINT_PHP72) {
                $this->windows_output = true;
            } else {
                $stream = self::$windows_stream;

                if (!$stream && \defined('STDOUT')) {
                    $stream = STDOUT;
                }

                if (!$stream) {
                    $this->windows_output = true;
                } else {
                    $this->windows_output = !\sapi_windows_vt100_support($stream);
                }
            }
        }

        if (!self::$terminal_width) {
            if (!KINT_WIN && self::$detect_width) {
                try {
                    self::$terminal_width = \exec('tput cols');
                } catch (Exception $e) {
                    self::$terminal_width = self::$default_width;
                } catch (Throwable $t) {
                    self::$terminal_width = self::$default_width;
                }
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

    public function renderTitle(Value $o)
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
            ['┌', '═', '┐', '│', '└', '─', '┘'],
            [' ', '=', ' ', '|', ' ', '-', ' '],
            $string
        );
    }
}
