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

namespace Kint\Value\Context;

use __PHP_Incomplete_Class;

abstract class ClassDeclaredContext extends ClassOwnedContext
{
    public const ACCESS_PUBLIC = 1;
    public const ACCESS_PROTECTED = 2;
    public const ACCESS_PRIVATE = 3;

    /** @psalm-var self::ACCESS_* */
    public int $access;

    /**
     * @psalm-param class-string $owner_class
     * @psalm-param self::ACCESS_* $access
     */
    public function __construct(string $name, string $owner_class, int $access)
    {
        parent::__construct($name, $owner_class);
        $this->access = $access;
    }

    abstract public function getModifiers(): string;

    /** @psalm-param ?class-string $scope */
    public function isAccessible(?string $scope): bool
    {
        if (__PHP_Incomplete_Class::class === $this->owner_class) {
            return false;
        }

        if (self::ACCESS_PUBLIC === $this->access) {
            return true;
        }

        if (null === $scope) {
            return false;
        }

        if (self::ACCESS_PRIVATE === $this->access) {
            return $scope === $this->owner_class;
        }

        if (\is_a($scope, $this->owner_class, true)) {
            return true;
        }

        if (\is_a($this->owner_class, $scope, true)) {
            return true;
        }

        return false;
    }

    protected function getAccess(): string
    {
        switch ($this->access) {
            case self::ACCESS_PUBLIC:
                return 'public';
            case self::ACCESS_PROTECTED:
                return 'protected';
            case self::ACCESS_PRIVATE:
                return 'private';
        }
    }
}
