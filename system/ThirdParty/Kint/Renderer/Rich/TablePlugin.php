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

namespace Kint\Renderer\Rich;

use Kint\Renderer\RichRenderer;
use Kint\Utils;
use Kint\Value\AbstractValue;
use Kint\Value\ArrayValue;
use Kint\Value\FixedWidthValue;
use Kint\Value\Representation\RepresentationInterface;
use Kint\Value\Representation\TableRepresentation;
use Kint\Value\StringValue;

class TablePlugin extends AbstractPlugin implements TabPluginInterface
{
    public static bool $respect_str_length = true;

    public function renderTab(RepresentationInterface $r, AbstractValue $v): ?string
    {
        if (!$r instanceof TableRepresentation) {
            return null;
        }

        $contents = $r->getContents();

        $firstrow = \reset($contents);

        if (!$firstrow instanceof ArrayValue) {
            return null;
        }

        $out = '<pre><table><thead><tr><th></th>';

        foreach ($firstrow->getContents() as $field) {
            $out .= '<th>'.$this->renderer->escape($field->getDisplayName()).'</th>';
        }

        $out .= '</tr></thead><tbody>';

        foreach ($contents as $row) {
            if (!$row instanceof ArrayValue) {
                return null;
            }

            $out .= '<tr><th>'.$this->renderer->escape($row->getDisplayName()).'</th>';

            foreach ($row->getContents() as $field) {
                $ref = $field->getContext()->isRef() ? '&amp;' : '';
                $type = $this->renderer->escape($field->getDisplayType());

                $out .= '<td title="'.$ref.$type;

                if (null !== ($size = $field->getDisplaySize())) {
                    $size = $this->renderer->escape($size);
                    $out .= ' ('.$size.')';
                }

                $out .= '">';

                if ($field instanceof FixedWidthValue) {
                    if (null === ($dv = $field->getDisplayValue())) {
                        $out .= '<var>'.$ref.'null</var>';
                    } elseif ('boolean' === $field->getType()) {
                        $out .= '<var>'.$ref.$dv.'</var>';
                    } else {
                        $out .= $dv;
                    }
                } elseif ($field instanceof StringValue) {
                    if (false !== $field->getEncoding()) {
                        $val = $field->getValueUtf8();

                        if (RichRenderer::$strlen_max && self::$respect_str_length) {
                            $val = Utils::truncateString($val, RichRenderer::$strlen_max, 'UTF-8');
                        }

                        $out .= $this->renderer->escape($val);
                    } else {
                        $out .= '<var>'.$ref.$type.'</var>';
                    }
                } elseif ($field instanceof ArrayValue) {
                    $out .= '<var>'.$ref.'array</var> ('.$field->getSize().')';
                } else {
                    $out .= '<var>'.$ref.$type.'</var>';
                    if (null !== $size) {
                        $out .= ' ('.$size.')';
                    }
                }

                if ($field->flags & AbstractValue::FLAG_BLACKLIST) {
                    $out .= ' <var>Blacklisted</var>';
                } elseif ($field->flags & AbstractValue::FLAG_RECURSION) {
                    $out .= ' <var>Recursion</var>';
                } elseif ($field->flags & AbstractValue::FLAG_DEPTH_LIMIT) {
                    $out .= ' <var>Depth Limit</var>';
                }

                $out .= '</td>';
            }

            $out .= '</tr>';
        }

        $out .= '</tbody></table></pre>';

        return $out;
    }
}
