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

namespace Kint\Object\Representation;

class SourceRepresentation extends Representation
{
    public $hints = array('source');
    public $source = array();
    public $filename;
    public $line = 0;
    public $showfilename = false;

    public function __construct($filename, $line, $padding = 7)
    {
        parent::__construct('Source');

        $this->filename = $filename;
        $this->line = $line;

        $start_line = \max($line - $padding, 1);
        $length = $line + $padding + 1 - $start_line;
        $this->source = self::getSource($filename, $start_line, $length);
        if (null !== $this->source) {
            $this->contents = \implode("\n", $this->source);
        }
    }

    /**
     * Gets section of source code.
     *
     * @param string   $filename   Full path to file
     * @param int      $start_line The first line to display (1 based)
     * @param null|int $length     Amount of lines to show
     *
     * @return null|array
     */
    public static function getSource($filename, $start_line = 1, $length = null)
    {
        if (!$filename || !\file_exists($filename) || !\is_readable($filename)) {
            return null;
        }

        $source = \preg_split("/\r\n|\n|\r/", \file_get_contents($filename));
        $source = \array_combine(\range(1, \count($source)), $source);
        $source = \array_slice($source, $start_line - 1, $length, true);

        return $source;
    }
}
