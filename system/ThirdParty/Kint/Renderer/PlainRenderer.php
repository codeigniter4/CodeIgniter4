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
use Kint\Object\BlobObject;

class PlainRenderer extends TextRenderer
{
    public static $pre_render_sources = array(
        'script' => array(
            array('Kint\\Renderer\\PlainRenderer', 'renderJs'),
            array('Kint\\Renderer\\Text\\MicrotimePlugin', 'renderJs'),
        ),
        'style' => array(
            array('Kint\\Renderer\\PlainRenderer', 'renderCss'),
        ),
        'raw' => array(),
    );

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
    protected $pre_render;

    public function __construct()
    {
        parent::__construct();

        $this->pre_render = self::$needs_pre_render;

        if (self::$always_pre_render) {
            $this->setPreRender(true);
        }
    }

    public function setCallInfo(array $info)
    {
        parent::setCallInfo($info);

        if (\in_array('@', $this->call_info['modifiers'], true)) {
            $this->setPreRender(true);
        }
    }

    public function setStatics(array $statics)
    {
        parent::setStatics($statics);

        if (!empty($statics['return'])) {
            $this->setPreRender(true);
        }
    }

    public function setPreRender($pre_render)
    {
        $this->pre_render = $pre_render;
        $this->force_pre_render = true;
    }

    public function getPreRender()
    {
        return $this->pre_render;
    }

    public function colorValue($string)
    {
        return '<i>'.$string.'</i>';
    }

    public function colorType($string)
    {
        return '<b>'.$string.'</b>';
    }

    public function colorTitle($string)
    {
        return '<u>'.$string.'</u>';
    }

    public function renderTitle(BasicObject $o)
    {
        if (self::$disable_utf8) {
            return $this->utf8ToHtmlentity(parent::renderTitle($o));
        }

        return parent::renderTitle($o);
    }

    public function preRender()
    {
        $output = '';

        if ($this->pre_render) {
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
            if (!$this->force_pre_render) {
                self::$needs_pre_render = false;
            }
        }

        return $output.'<div class="kint-plain">';
    }

    public function postRender()
    {
        if (self::$disable_utf8) {
            return $this->utf8ToHtmlentity(parent::postRender()).'</div>';
        }

        return parent::postRender().'</div>';
    }

    public function ideLink($file, $line)
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

    public function escape($string, $encoding = false)
    {
        if (false === $encoding) {
            $encoding = BlobObject::detectEncoding($string);
        }

        $original_encoding = $encoding;

        if (false === $encoding || 'ASCII' === $encoding) {
            $encoding = 'UTF-8';
        }

        $string = \htmlspecialchars($string, ENT_NOQUOTES, $encoding);

        // this call converts all non-ASCII characters into numeirc htmlentities
        if (\function_exists('mb_encode_numericentity') && 'ASCII' !== $original_encoding) {
            $string = \mb_encode_numericentity($string, array(0x80, 0xffff, 0, 0xffff), $encoding);
        }

        return $string;
    }

    protected function utf8ToHtmlentity($string)
    {
        return \str_replace(
            array('┌', '═', '┐', '│', '└', '─', '┘'),
            array('&#9484;', '&#9552;', '&#9488;', '&#9474;', '&#9492;', '&#9472;', '&#9496;'),
            $string
        );
    }

    protected static function renderJs()
    {
        return \file_get_contents(KINT_DIR.'/resources/compiled/shared.js').\file_get_contents(KINT_DIR.'/resources/compiled/plain.js');
    }

    protected static function renderCss()
    {
        if (\file_exists(KINT_DIR.'/resources/compiled/'.self::$theme)) {
            return \file_get_contents(KINT_DIR.'/resources/compiled/'.self::$theme);
        }

        return \file_get_contents(self::$theme);
    }
}
