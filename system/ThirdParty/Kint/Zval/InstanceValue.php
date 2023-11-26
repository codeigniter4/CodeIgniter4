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

namespace Kint\Zval;

class InstanceValue extends Value
{
    public $type = 'object';
    public $classname;
    public $spl_object_hash;
    public $spl_object_id = null;
    public $filename;
    public $startline;
    public $hints = ['object'];

    public function getType(): ?string
    {
        return $this->classname;
    }

    public function transplant(Value $old): void
    {
        parent::transplant($old);

        if ($old instanceof self) {
            $this->classname = $old->classname;
            $this->spl_object_hash = $old->spl_object_hash;
            $this->spl_object_id = $old->spl_object_id;
            $this->filename = $old->filename;
            $this->startline = $old->startline;
        }
    }

    /**
     * @psalm-param  class-string $a
     * @psalm-param  class-string $b
     */
    public static function sortByHierarchy(string $a, string $b): int
    {
        if (\is_subclass_of($a, $b)) {
            return -1;
        }

        if (\is_subclass_of($b, $a)) {
            return 1;
        }

        return 0;
    }
}
