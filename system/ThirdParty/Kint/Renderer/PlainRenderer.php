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

namespace Kint\Renderer;

use Kint\Kint;
use Kint\Zval\BlobValue;
use Kint\Zval\Value;

class PlainRenderer extends TextRenderer
{
    public static $pre_render_sources = [
        'script' => [
            ['Kint\\Renderer\\PlainRenderer', 'renderJs'],
            ['Kint\\Renderer\\Text\\MicrotimePlugin', 'renderJs'],
        ],
        'style' => [
            ['Kint\\Renderer\\PlainRenderer', 'renderCss'],
        ],
        'raw' => [],
    ];

    /**
     * Path to the CSS file to load by default.
     *
     * @var string
     */
    public static $theme = 'plain.css';

    /**
     * Output htmlentities instead of utf8.
     *
     * @var bool
     */
    public static $disable_utf8 = false;

    public static $needs_pre_render = true;

    public static $always_pre_render = false;

    protected $force_pre_render = false;

    public function __construct()
    {
        parent::__construct();
        $this->setForcePreRender(self::$always_pre_render);
    }

    public function setCallInfo(array $info): void
    {
        parent::setCallInfo($info);

        if (\in_array('@', $this->call_info['modifiers'], true)) {
            $this->setForcePreRender(true);
        }
    }

    public function setStatics(array $statics): void
    {
        parent::setStatics($statics);

        if (!empty($statics['return'])) {
            $this->setForcePreRender(true);
        }
    }

    public function setForcePreRender(bool $force_pre_render): void
    {
        $this->force_pre_render = $force_pre_render;
    }

    public function getForcePreRender(): bool
    {
        return $this->force_pre_render;
    }

    public function shouldPreRender(): bool
    {
        return $this->getForcePreRender() || self::$needs_pre_render;
    }

    public function colorValue(string $string): string
    {
        return '<i>'.$string.'</i>';
    }

    public function colorType(string $string): string
    {
        return '<b>'.$string.'</b>';
    }

    public function colorTitle(string $string): string
    {
        return '<u>'.$string.'</u>';
    }

    public function renderTitle(Value $o): string
    {
        if (self::$disable_utf8) {
            return $this->utf8ToHtmlentity(parent::renderTitle($o));
        }

        return parent::renderTitle($o);
    }

    public function preRender(): string
    {
        $output = '';

        if ($this->shouldPreRender()) {
            foreach (self::$pre_render_sources as $type => $values) {
                $contents = '';
                foreach ($values as $v) {
                    $contents .= \call_user_func($v, $this);
                }

                if (!\strlen($contents)) {
                    continue;
                }

                switch ($type) {
                    case 'script':
                        $output .= '<script class="kint-plain-script">'.$contents.'</script>';
                        break;
                    case 'style':
                        $output .= '<style class="kint-plain-style">'.$contents.'</style>';
                        break;
                    default:
                        $output .= $contents;
                }
            }

            // Don't pre-render on every dump
            if (!$this->getForcePreRender()) {
                self::$needs_pre_render = false;
            }
        }

        return $output.'<div class="kint-plain">';
    }

    public function postRender(): string
    {
        if (self::$disable_utf8) {
            return $this->utf8ToHtmlentity(parent::postRender()).'</div>';
        }

        return parent::postRender().'</div>';
    }

    public function ideLink(string $file, int $line): string
    {
        $path = $this->escape(Kint::shortenPath($file)).':'.$line;
        $ideLink = Kint::getIdeLink($file, $line);

        if (!$ideLink) {
            return $path;
        }

        $class = '';

        if (\preg_match('/https?:\\/\\//i', $ideLink)) {
            $class = 'class="kint-ide-link" ';
        }

        return '<a '.$class.'href="'.$this->escape($ideLink).'">'.$path.'</a>';
    }

    public function escape(string $string, $encoding = false): string
    {
        if (false === $encoding) {
            $encoding = BlobValue::detectEncoding($string);
        }

        $original_encoding = $encoding;

        if (false === $encoding || 'ASCII' === $encoding) {
            $encoding = 'UTF-8';
        }

        $string = \htmlspecialchars($string, ENT_NOQUOTES, $encoding);

        // this call converts all non-ASCII characters into numeirc htmlentities
        if (\function_exists('mb_encode_numericentity') && 'ASCII' !== $original_encoding) {
            $string = \mb_encode_numericentity($string, [0x80, 0xFFFF, 0, 0xFFFF], $encoding);
        }

        return $string;
    }

    protected function utf8ToHtmlentity(string $string): string
    {
        return \str_replace(
            ['┌', '═', '┐', '│', '└', '─', '┘'],
            ['&#9484;', '&#9552;', '&#9488;', '&#9474;', '&#9492;', '&#9472;', '&#9496;'],
            $string
        );
    }

    protected static function renderJs(): string
    {
        return \file_get_contents(KINT_DIR.'/resources/compiled/shared.js').\file_get_contents(KINT_DIR.'/resources/compiled/plain.js');
    }

    protected static function renderCss(): string
    {
        if (\file_exists(KINT_DIR.'/resources/compiled/'.self::$theme)) {
            return \file_get_contents(KINT_DIR.'/resources/compiled/'.self::$theme);
        }

        return \file_get_contents(self::$theme);
    }
}
