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

use Kint\Object\Representation\Representation;

class BasicObject
{
    const ACCESS_NONE = null;
    const ACCESS_PUBLIC = 1;
    const ACCESS_PROTECTED = 2;
    const ACCESS_PRIVATE = 3;

    const OPERATOR_NONE = null;
    const OPERATOR_ARRAY = 1;
    const OPERATOR_OBJECT = 2;
    const OPERATOR_STATIC = 3;

    public $name;
    public $type;
    public $static = false;
    public $const = false;
    public $access = self::ACCESS_NONE;
    public $owner_class;
    public $access_path;
    public $operator = self::OPERATOR_NONE;
    public $reference = false;
    public $depth = 0;
    public $size;
    public $value;
    public $hints = array();

    protected $representations = array();

    public function __construct()
    {
    }

    public function addRepresentation(Representation $rep, $pos = null)
    {
        if (isset($this->representations[$rep->getName()])) {
            return false;
        }

        if (null === $pos) {
            $this->representations[$rep->getName()] = $rep;
        } else {
            $this->representations = \array_merge(
                \array_slice($this->representations, 0, $pos),
                array($rep->getName() => $rep),
                \array_slice($this->representations, $pos)
            );
        }

        return true;
    }

    public function replaceRepresentation(Representation $rep, $pos = null)
    {
        if (null === $pos) {
            $this->representations[$rep->getName()] = $rep;
        } else {
            $this->removeRepresentation($rep);
            $this->addRepresentation($rep, $pos);
        }
    }

    public function removeRepresentation($rep)
    {
        if ($rep instanceof Representation) {
            unset($this->representations[$rep->getName()]);
        } elseif (\is_string($rep)) {
            unset($this->representations[$rep]);
        }
    }

    public function getRepresentation($name)
    {
        if (isset($this->representations[$name])) {
            return $this->representations[$name];
        }
    }

    public function getRepresentations()
    {
        return $this->representations;
    }

    public function clearRepresentations()
    {
        $this->representations = array();
    }

    public function getType()
    {
        return $this->type;
    }

    public function getModifiers()
    {
        $out = $this->getAccess();

        if ($this->const) {
            $out .= ' const';
        }

        if ($this->static) {
            $out .= ' static';
        }

        if (\strlen($out)) {
            return \ltrim($out);
        }
    }

    public function getAccess()
    {
        switch ($this->access) {
            case self::ACCESS_PRIVATE:
                return 'private';
            case self::ACCESS_PROTECTED:
                return 'protected';
            case self::ACCESS_PUBLIC:
                return 'public';
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOperator()
    {
        switch ($this->operator) {
            case self::OPERATOR_ARRAY:
                return '=>';
            case self::OPERATOR_OBJECT:
                return '->';
            case self::OPERATOR_STATIC:
                return '::';
        }
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getValueShort()
    {
        if ($rep = $this->value) {
            if ('boolean' === $this->type) {
                return $rep->contents ? 'true' : 'false';
            }

            if ('integer' === $this->type || 'double' === $this->type) {
                return $rep->contents;
            }
        }
    }

    public function getAccessPath()
    {
        return $this->access_path;
    }

    public function transplant(BasicObject $old)
    {
        $this->name = $old->name;
        $this->size = $old->size;
        $this->access_path = $old->access_path;
        $this->access = $old->access;
        $this->static = $old->static;
        $this->const = $old->const;
        $this->type = $old->type;
        $this->depth = $old->depth;
        $this->owner_class = $old->owner_class;
        $this->operator = $old->operator;
        $this->reference = $old->reference;
        $this->value = $old->value;
        $this->representations += $old->representations;
        $this->hints = \array_merge($this->hints, $old->hints);
    }

    /**
     * Creates a new basic object with a name and access path.
     *
     * @param null|string $name
     * @param null|string $access_path
     *
     * @return \Kint\Object\BasicObject
     */
    public static function blank($name = null, $access_path = null)
    {
        $o = new self();
        $o->name = $name;
        $o->access_path = $access_path;

        return $o;
    }

    public static function sortByAccess(BasicObject $a, BasicObject $b)
    {
        static $sorts = array(
            self::ACCESS_PUBLIC => 1,
            self::ACCESS_PROTECTED => 2,
            self::ACCESS_PRIVATE => 3,
            self::ACCESS_NONE => 4,
        );

        return $sorts[$a->access] - $sorts[$b->access];
    }

    public static function sortByName(BasicObject $a, BasicObject $b)
    {
        $ret = \strnatcasecmp($a->name, $b->name);

        if (0 === $ret) {
            return (int) \is_int($b->name) - (int) \is_int($a->name);
        }

        return $ret;
    }
}
