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

class PropertyContext extends DoubleAccessMemberContext
{
    public const HOOK_NONE = 0;
    public const HOOK_GET = 1 << 0;
    public const HOOK_GET_REF = 1 << 1;
    public const HOOK_SET = 1 << 2;
    public const HOOK_SET_TYPE = 1 << 3;

    public bool $readonly = false;
    /** @psalm-var int-mask-of<self::HOOK_*> */
    public int $hooks = self::HOOK_NONE;
    public ?string $hook_set_type = null;

    public function getModifiers(): string
    {
        $out = $this->getAccess();

        if ($this->readonly) {
            $out .= ' readonly';
        }

        return $out;
    }

    public function getHooks(): ?string
    {
        if (self::HOOK_NONE === $this->hooks) {
            return null;
        }

        $out = '{ ';
        if ($this->hooks & self::HOOK_GET) {
            if ($this->hooks & self::HOOK_GET_REF) {
                $out .= '&';
            }
            $out .= 'get; ';
        }
        if ($this->hooks & self::HOOK_SET) {
            if ($this->hooks & self::HOOK_SET_TYPE && '' !== ($this->hook_set_type ?? '')) {
                $out .= 'set('.$this->hook_set_type.'); ';
            } else {
                $out .= 'set; ';
            }
        }
        $out .= '}';

        return $out;
    }
}
