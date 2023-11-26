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

use Kint\Utils;
use ReflectionParameter;

class ParameterValue extends Value
{
    public $type_hint;
    public $default;
    public $position;
    public $hints = ['parameter'];

    public function __construct(ReflectionParameter $param)
    {
        parent::__construct();

        if ($type = $param->getType()) {
            $this->type_hint = Utils::getTypeString($type);
        }

        $this->reference = $param->isPassedByReference();
        $this->name = $param->getName();
        $this->position = $param->getPosition();

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
                default:
                    $this->default = \var_export($default, true);
                    break;
            }
        }
    }

    public function getType(): ?string
    {
        return $this->type_hint;
    }

    public function getName(): string
    {
        return '$'.$this->name;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }
}
