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

abstract class AbstractRenderer implements ConstructableRendererInterface
{
    public static ?string $js_nonce = null;
    public static ?string $css_nonce = null;

    /** @psalm-var ?non-empty-string */
    public static ?string $file_link_format = null;

    protected bool $show_trace = true;
    protected ?array $callee = null;
    protected array $trace = [];

    protected bool $render_spl_ids = true;

    public function __construct()
    {
    }

    public function shouldRenderObjectIds(): bool
    {
        return $this->render_spl_ids;
    }

    public function setCallInfo(array $info): void
    {
        $this->callee = $info['callee'] ?? null;
        $this->trace = $info['trace'] ?? [];
    }

    public function setStatics(array $statics): void
    {
        $this->show_trace = !empty($statics['display_called_from']);
    }

    public function filterParserPlugins(array $plugins): array
    {
        return $plugins;
    }

    public function preRender(): string
    {
        return '';
    }

    public function postRender(): string
    {
        return '';
    }

    public static function getFileLink(string $file, int $line): ?string
    {
        if (null === self::$file_link_format) {
            return null;
        }

        return \str_replace(['%f', '%l'], [$file, $line], self::$file_link_format);
    }
}
