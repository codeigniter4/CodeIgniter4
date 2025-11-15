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

namespace Kint\Value;

use Kint\Utils;
use ReflectionParameter;

final class ParameterBag
{
    /** @psalm-readonly */
    public string $name;
    /** @psalm-readonly */
    public int $position;
    /** @psalm-readonly */
    public bool $ref;
    /** @psalm-readonly */
    public ?string $type_hint;
    /** @psalm-readonly */
    public ?string $default;

    public function __construct(ReflectionParameter $param)
    {
        $this->name = $param->getName();
        $this->position = $param->getPosition();
        $this->ref = $param->isPassedByReference();

        $this->type_hint = ($type = $param->getType()) ? Utils::getTypeString($type) : null;

        if ($param->isDefaultValueAvailable()) {
            $default = $param->getDefaultValue();
            switch (\gettype($default)) {
                case 'NULL':
                    $this->default = 'null';
                    break;
                case 'boolean':
                    $this->default = $default ? 'true' : 'false';
                    break;
                case 'array':
                    $this->default = \count($default) ? 'array(...)' : 'array()';
                    break;
                case 'double':
                case 'integer':
                case 'string':
                    $this->default = \var_export($default, true);
                    break;
                case 'object':
                    $this->default = 'object('.\get_class($default).')';
                    break;
                default:
                    $this->default = \gettype($default);
                    break;
            }
        } else {
            $this->default = null;
        }
    }

    public function __toString()
    {
        $type = $this->type_hint;
        if (null !== $type) {
            $type .= ' ';
        }

        $default = $this->default;
        if (null !== $default) {
            $default = ' = '.$default;
        }

        $ref = $this->ref ? '&' : '';

        return $type.$ref.'$'.$this->name.$default;
    }
}
