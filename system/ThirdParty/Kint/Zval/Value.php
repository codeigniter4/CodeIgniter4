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

use Kint\Zval\Representation\Representation;

class Value
{
    public const ACCESS_NONE = null;
    public const ACCESS_PUBLIC = 1;
    public const ACCESS_PROTECTED = 2;
    public const ACCESS_PRIVATE = 3;

    public const OPERATOR_NONE = null;
    public const OPERATOR_ARRAY = 1;
    public const OPERATOR_OBJECT = 2;
    public const OPERATOR_STATIC = 3;

    public $name;
    public $type;
    public $readonly = false;
    public $static = false;
    public $const = false;
    public $access = self::ACCESS_NONE;
    public $owner_class = null;
    public $access_path;
    public $operator = self::OPERATOR_NONE;
    public $reference = false;
    public $depth = 0;
    public $size;
    public $hints = [];
    public $value;

    protected $representations = [];

    public function __construct()
    {
    }

    public function addRepresentation(Representation $rep, int $pos = null): bool
    {
        if (isset($this->representations[$rep->getName()])) {
            return false;
        }

        if (null === $pos) {
            $this->representations[$rep->getName()] = $rep;
        } else {
            $this->representations = \array_merge(
                \array_slice($this->representations, 0, $pos),
                [$rep->getName() => $rep],
                \array_slice($this->representations, $pos)
            );
        }

        return true;
    }

    public function replaceRepresentation(Representation $rep, int $pos = null): void
    {
        if (null === $pos) {
            $this->representations[$rep->getName()] = $rep;
        } else {
            $this->removeRepresentation($rep);
            $this->addRepresentation($rep, $pos);
        }
    }

    /**
     * @param Representation|string $rep
     */
    public function removeRepresentation($rep): void
    {
        if ($rep instanceof Representation) {
            unset($this->representations[$rep->getName()]);
        } else { // String
            unset($this->representations[$rep]);
        }
    }

    public function getRepresentation(string $name): ?Representation
    {
        return $this->representations[$name] ?? null;
    }

    public function getRepresentations(): array
    {
        return $this->representations;
    }

    public function clearRepresentations(): void
    {
        $this->representations = [];
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getModifiers(): ?string
    {
        $out = $this->getAccess();

        if ($this->readonly) {
            $out .= ' readonly';
        }

        if ($this->const) {
            $out .= ' const';
        }

        if ($this->static) {
            $out .= ' static';
        }

        if (null !== $out && \strlen($out)) {
            return \ltrim($out);
        }

        return null;
    }

    public function getAccess(): ?string
    {
        switch ($this->access) {
            case self::ACCESS_PRIVATE:
                return 'private';
            case self::ACCESS_PROTECTED:
                return 'protected';
            case self::ACCESS_PUBLIC:
                return 'public';
        }

        return null;
    }

    public function getName(): ?string
    {
        if (isset($this->name)) {
            return (string) $this->name;
        }

        return null;
    }

    public function getOperator(): ?string
    {
        switch ($this->operator) {
            case self::OPERATOR_ARRAY:
                return '=>';
            case self::OPERATOR_OBJECT:
                return '->';
            case self::OPERATOR_STATIC:
                return '::';
        }

        return null;
    }

    public function getSize(): ?string
    {
        if (isset($this->size)) {
            return (string) $this->size;
        }

        return null;
    }

    public function getValueShort(): ?string
    {
        if ($rep = $this->value) {
            if ('boolean' === $this->type) {
                return $rep->contents ? 'true' : 'false';
            }

            if ('integer' === $this->type || 'double' === $this->type) {
                return (string) $rep->contents;
            }
        }

        return null;
    }

    public function getAccessPath(): ?string
    {
        return $this->access_path;
    }

    public function transplant(self $old): void
    {
        $this->name = $old->name;
        $this->size = $old->size;
        $this->access_path = $old->access_path;
        $this->access = $old->access;
        $this->readonly = $old->readonly;
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
     */
    public static function blank(string $name = null, string $access_path = null): self
    {
        $o = new self();
        $o->name = $name;
        $o->access_path = $access_path;

        return $o;
    }

    public static function sortByAccess(self $a, self $b): int
    {
        static $sorts = [
            self::ACCESS_PUBLIC => 1,
            self::ACCESS_PROTECTED => 2,
            self::ACCESS_PRIVATE => 3,
            self::ACCESS_NONE => 4,
        ];

        return $sorts[$a->access] - $sorts[$b->access];
    }

    public static function sortByName(self $a, self $b): int
    {
        if ((string) $a->name === (string) $b->name) {
            return (int) \is_int($b->name) - (int) \is_int($a->name);
        }

        return \strnatcasecmp((string) $a->name, (string) $b->name);
    }
}
