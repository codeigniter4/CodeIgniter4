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

namespace Kint\Zval\Representation;

class MethodDefinitionRepresentation extends Representation
{
    public $file;
    public $line;
    public $class;
    public $inherited = false;
    public $hints = ['method_definition'];

    public function __construct(?string $file, ?int $line, ?string $class, ?string $docstring)
    {
        parent::__construct('Method definition');

        $this->file = $file;
        $this->line = $line;
        $this->class = $class;
        $this->contents = $docstring;
    }

    /**
     * Returns the representation's docstring without surrounding comments.
     *
     * Note that this will not work flawlessly.
     *
     * On comments with whitespace after the stars the lines will begin with
     * whitespace, since we can't accurately guess how much of an indentation
     * is required.
     *
     * And on lines without stars on the left this may eat bullet points.
     *
     * Long story short: If you want the docstring read the contents. If you
     * absolutely must have it without comments (ie renderValueShort) this will
     * probably do.
     *
     * @return ?string Docstring with comments stripped
     */
    public function getDocstringWithoutComments()
    {
        if (!$this->contents) {
            return null;
        }

        $string = \substr($this->contents, 3, -2);
        $string = \preg_replace('/^\\s*\\*\\s*?(\\S|$)/m', '\\1', $string);

        return \trim($string);
    }
}
