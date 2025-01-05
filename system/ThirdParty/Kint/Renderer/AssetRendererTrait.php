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

namespace Kint\Renderer;

trait AssetRendererTrait
{
    public static ?string $theme = null;

    /** @psalm-var array{js?:string, css?:array<path, string|false>} */
    private static array $assetCache = [];

    /** @psalm-api */
    public static function renderJs(): string
    {
        if (!isset(self::$assetCache['js'])) {
            self::$assetCache['js'] = \file_get_contents(KINT_DIR.'/resources/compiled/main.js');
        }

        return self::$assetCache['js'];
    }

    /** @psalm-api */
    public static function renderCss(): ?string
    {
        if (!isset(self::$theme)) {
            return null;
        }

        if (!isset(self::$assetCache['css'][self::$theme])) {
            if (\file_exists(KINT_DIR.'/resources/compiled/'.self::$theme)) {
                self::$assetCache['css'][self::$theme] = \file_get_contents(KINT_DIR.'/resources/compiled/'.self::$theme);
            } elseif (\file_exists(self::$theme)) {
                self::$assetCache['css'][self::$theme] = \file_get_contents(self::$theme);
            } else {
                self::$assetCache['css'][self::$theme] = false;
            }
        }

        if (false === self::$assetCache['css'][self::$theme]) {
            return null;
        }

        return self::$assetCache['css'][self::$theme];
    }
}
