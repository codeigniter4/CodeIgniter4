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

namespace Kint\Object;

use Kint\Object\Representation\DocstringRepresentation;
use Kint\Utils;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class MethodObject extends BasicObject
{
    public $type = 'method';
    public $filename;
    public $startline;
    public $endline;
    public $parameters = array();
    public $abstract;
    public $final;
    public $internal;
    public $docstring;
    public $returntype;
    public $return_reference = false;
    public $hints = array('callable', 'method');
    public $showparams = true;

    private $paramcache;

    public function __construct(ReflectionFunctionAbstract $method)
    {
        parent::__construct();

        $this->name = $method->getName();
        $this->filename = $method->getFileName();
        $this->startline = $method->getStartLine();
        $this->endline = $method->getEndLine();
        $this->internal = $method->isInternal();
        $this->docstring = $method->getDocComment();
        $this->return_reference = $method->returnsReference();

        foreach ($method->getParameters() as $param) {
            $this->parameters[] = new ParameterObject($param);
        }

        if (KINT_PHP70) {
            $this->returntype = $method->getReturnType();
            if ($this->returntype) {
                $this->returntype = Utils::getTypeString($this->returntype);
            }
        }

        if ($method instanceof ReflectionMethod) {
            $this->static = $method->isStatic();
            $this->operator = $this->static ? BasicObject::OPERATOR_STATIC : BasicObject::OPERATOR_OBJECT;
            $this->abstract = $method->isAbstract();
            $this->final = $method->isFinal();
            $this->owner_class = $method->getDeclaringClass()->name;
            $this->access = BasicObject::ACCESS_PUBLIC;
            if ($method->isProtected()) {
                $this->access = BasicObject::ACCESS_PROTECTED;
            } elseif ($method->isPrivate()) {
                $this->access = BasicObject::ACCESS_PRIVATE;
            }
        }

        if ($this->internal) {
            return;
        }

        $docstring = new DocstringRepresentation(
            $this->docstring,
            $this->filename,
            $this->startline
        );

        $docstring->implicit_label = true;
        $this->addRepresentation($docstring);
        $this->value = $docstring;
    }

    public function setAccessPathFrom(InstanceObject $parent)
    {
        static $magic = array(
            '__call' => true,
            '__callstatic' => true,
            '__clone' => true,
            '__construct' => true,
            '__debuginfo' => true,
            '__destruct' => true,
            '__get' => true,
            '__invoke' => true,
            '__isset' => true,
            '__set' => true,
            '__set_state' => true,
            '__sleep' => true,
            '__tostring' => true,
            '__unset' => true,
            '__wakeup' => true,
        );

        $name = \strtolower($this->name);

        if ('__construct' === $name) {
            $this->access_path = 'new \\'.$parent->getType();
        } elseif ('__invoke' === $name) {
            $this->access_path = $parent->access_path;
        } elseif ('__clone' === $name) {
            $this->access_path = 'clone '.$parent->access_path;
            $this->showparams = false;
        } elseif ('__tostring' === $name) {
            $this->access_path = '(string) '.$parent->access_path;
            $this->showparams = false;
        } elseif (isset($magic[$name])) {
            $this->access_path = null;
        } elseif ($this->static) {
            $this->access_path = '\\'.$this->owner_class.'::'.$this->name;
        } else {
            $this->access_path = $parent->access_path.'->'.$this->name;
        }
    }

    public function getValueShort()
    {
        if (!$this->value || !($this->value instanceof DocstringRepresentation)) {
            return parent::getValueShort();
        }

        $ds = $this->value->getDocstringWithoutComments();

        if (!$ds) {
            return null;
        }

        $ds = \explode("\n", $ds);

        $out = '';

        foreach ($ds as $line) {
            if (0 === \strlen(\trim($line)) || '@' === $line[0]) {
                break;
            }

            $out .= $line.' ';
        }

        if (\strlen($out)) {
            return \rtrim($out);
        }
    }

    public function getModifiers()
    {
        $mods = array(
            $this->abstract ? 'abstract' : null,
            $this->final ? 'final' : null,
            $this->getAccess(),
            $this->static ? 'static' : null,
        );

        $out = '';

        foreach ($mods as $word) {
            if (null !== $word) {
                $out .= $word.' ';
            }
        }

        if (\strlen($out)) {
            return \rtrim($out);
        }
    }

    public function getAccessPath()
    {
        if (null !== $this->access_path) {
            if ($this->showparams) {
                return parent::getAccessPath().'('.$this->getParams().')';
            }

            return parent::getAccessPath();
        }
    }

    public function getParams()
    {
        if (null !== $this->paramcache) {
            return $this->paramcache;
        }

        $out = array();

        foreach ($this->parameters as $p) {
            $type = $p->getType();
            if ($type) {
                $type .= ' ';
            }

            $default = $p->getDefault();
            if ($default) {
                $default = ' = '.$default;
            }

            $ref = $p->reference ? '&' : '';

            $out[] = $type.$ref.$p->getName().$default;
        }

        return $this->paramcache = \implode(', ', $out);
    }

    public function getPhpDocUrl()
    {
        if (!$this->internal) {
            return null;
        }

        if ($this->owner_class) {
            $class = \strtolower($this->owner_class);
        } else {
            $class = 'function';
        }

        $funcname = \str_replace('_', '-', \strtolower($this->name));

        if (0 === \strpos($funcname, '--') && 0 !== \strpos($funcname, '-', 2)) {
            $funcname = \substr($funcname, 2);
        }

        return 'https://secure.php.net/'.$class.'.'.$funcname;
    }
}
