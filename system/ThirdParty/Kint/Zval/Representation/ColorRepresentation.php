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

namespace Kint\Zval\Representation;

use InvalidArgumentException;

class ColorRepresentation extends Representation
{
    public const COLOR_NAME = 1;
    public const COLOR_HEX_3 = 2;
    public const COLOR_HEX_6 = 3;
    public const COLOR_RGB = 4;
    public const COLOR_RGBA = 5;
    public const COLOR_HSL = 6;
    public const COLOR_HSLA = 7;
    public const COLOR_HEX_4 = 8;
    public const COLOR_HEX_8 = 9;

    public static $color_map = [
        'aliceblue' => 'f0f8ff',
        'antiquewhite' => 'faebd7',
        'aqua' => '00ffff',
        'aquamarine' => '7fffd4',
        'azure' => 'f0ffff',
        'beige' => 'f5f5dc',
        'bisque' => 'ffe4c4',
        'black' => '000000',
        'blanchedalmond' => 'ffebcd',
        'blue' => '0000ff',
        'blueviolet' => '8a2be2',
        'brown' => 'a52a2a',
        'burlywood' => 'deb887',
        'cadetblue' => '5f9ea0',
        'chartreuse' => '7fff00',
        'chocolate' => 'd2691e',
        'coral' => 'ff7f50',
        'cornflowerblue' => '6495ed',
        'cornsilk' => 'fff8dc',
        'crimson' => 'dc143c',
        'cyan' => '00ffff',
        'darkblue' => '00008b',
        'darkcyan' => '008b8b',
        'darkgoldenrod' => 'b8860b',
        'darkgray' => 'a9a9a9',
        'darkgreen' => '006400',
        'darkgrey' => 'a9a9a9',
        'darkkhaki' => 'bdb76b',
        'darkmagenta' => '8b008b',
        'darkolivegreen' => '556b2f',
        'darkorange' => 'ff8c00',
        'darkorchid' => '9932cc',
        'darkred' => '8b0000',
        'darksalmon' => 'e9967a',
        'darkseagreen' => '8fbc8f',
        'darkslateblue' => '483d8b',
        'darkslategray' => '2f4f4f',
        'darkslategrey' => '2f4f4f',
        'darkturquoise' => '00ced1',
        'darkviolet' => '9400d3',
        'deeppink' => 'ff1493',
        'deepskyblue' => '00bfff',
        'dimgray' => '696969',
        'dimgrey' => '696969',
        'dodgerblue' => '1e90ff',
        'firebrick' => 'b22222',
        'floralwhite' => 'fffaf0',
        'forestgreen' => '228b22',
        'fuchsia' => 'ff00ff',
        'gainsboro' => 'dcdcdc',
        'ghostwhite' => 'f8f8ff',
        'gold' => 'ffd700',
        'goldenrod' => 'daa520',
        'gray' => '808080',
        'green' => '008000',
        'greenyellow' => 'adff2f',
        'grey' => '808080',
        'honeydew' => 'f0fff0',
        'hotpink' => 'ff69b4',
        'indianred' => 'cd5c5c',
        'indigo' => '4b0082',
        'ivory' => 'fffff0',
        'khaki' => 'f0e68c',
        'lavender' => 'e6e6fa',
        'lavenderblush' => 'fff0f5',
        'lawngreen' => '7cfc00',
        'lemonchiffon' => 'fffacd',
        'lightblue' => 'add8e6',
        'lightcoral' => 'f08080',
        'lightcyan' => 'e0ffff',
        'lightgoldenrodyellow' => 'fafad2',
        'lightgray' => 'd3d3d3',
        'lightgreen' => '90ee90',
        'lightgrey' => 'd3d3d3',
        'lightpink' => 'ffb6c1',
        'lightsalmon' => 'ffa07a',
        'lightseagreen' => '20b2aa',
        'lightskyblue' => '87cefa',
        'lightslategray' => '778899',
        'lightslategrey' => '778899',
        'lightsteelblue' => 'b0c4de',
        'lightyellow' => 'ffffe0',
        'lime' => '00ff00',
        'limegreen' => '32cd32',
        'linen' => 'faf0e6',
        'magenta' => 'ff00ff',
        'maroon' => '800000',
        'mediumaquamarine' => '66cdaa',
        'mediumblue' => '0000cd',
        'mediumorchid' => 'ba55d3',
        'mediumpurple' => '9370db',
        'mediumseagreen' => '3cb371',
        'mediumslateblue' => '7b68ee',
        'mediumspringgreen' => '00fa9a',
        'mediumturquoise' => '48d1cc',
        'mediumvioletred' => 'c71585',
        'midnightblue' => '191970',
        'mintcream' => 'f5fffa',
        'mistyrose' => 'ffe4e1',
        'moccasin' => 'ffe4b5',
        'navajowhite' => 'ffdead',
        'navy' => '000080',
        'oldlace' => 'fdf5e6',
        'olive' => '808000',
        'olivedrab' => '6b8e23',
        'orange' => 'ffa500',
        'orangered' => 'ff4500',
        'orchid' => 'da70d6',
        'palegoldenrod' => 'eee8aa',
        'palegreen' => '98fb98',
        'paleturquoise' => 'afeeee',
        'palevioletred' => 'db7093',
        'papayawhip' => 'ffefd5',
        'peachpuff' => 'ffdab9',
        'peru' => 'cd853f',
        'pink' => 'ffc0cb',
        'plum' => 'dda0dd',
        'powderblue' => 'b0e0e6',
        'purple' => '800080',
        'rebeccapurple' => '663399',
        'red' => 'ff0000',
        'rosybrown' => 'bc8f8f',
        'royalblue' => '4169e1',
        'saddlebrown' => '8b4513',
        'salmon' => 'fa8072',
        'sandybrown' => 'f4a460',
        'seagreen' => '2e8b57',
        'seashell' => 'fff5ee',
        'sienna' => 'a0522d',
        'silver' => 'c0c0c0',
        'skyblue' => '87ceeb',
        'slateblue' => '6a5acd',
        'slategray' => '708090',
        'slategrey' => '708090',
        'snow' => 'fffafa',
        'springgreen' => '00ff7f',
        'steelblue' => '4682b4',
        'tan' => 'd2b48c',
        'teal' => '008080',
        'thistle' => 'd8bfd8',
        'tomato' => 'ff6347',
        // To quote MDN:
        // "Technically, transparent is a shortcut for rgba(0,0,0,0)."
        'transparent' => '00000000',
        'turquoise' => '40e0d0',
        'violet' => 'ee82ee',
        'wheat' => 'f5deb3',
        'white' => 'ffffff',
        'whitesmoke' => 'f5f5f5',
        'yellow' => 'ffff00',
        'yellowgreen' => '9acd32',
    ];

    public $r = 0;
    public $g = 0;
    public $b = 0;
    public $a = 1.0;
    public $variant;
    public $implicit_label = true;
    public $hints = ['color'];

    public function __construct(string $value)
    {
        parent::__construct('Color');

        $this->contents = $value;
        $this->setValues($value);
    }

    public function getColor(int $variant = null): ?string
    {
        if (!$variant) {
            $variant = $this->variant;
        }

        switch ($variant) {
            case self::COLOR_NAME:
                $hex = \sprintf('%02x%02x%02x', $this->r, $this->g, $this->b);
                $hex_alpha = \sprintf('%02x%02x%02x%02x', $this->r, $this->g, $this->b, \round($this->a * 0xFF));

                return \array_search($hex, self::$color_map, true) ?: \array_search($hex_alpha, self::$color_map, true) ?: null;
            case self::COLOR_HEX_3:
                if (0 === $this->r % 0x11 && 0 === $this->g % 0x11 && 0 === $this->b % 0x11) {
                    return \sprintf(
                        '#%1X%1X%1X',
                        \round($this->r / 0x11),
                        \round($this->g / 0x11),
                        \round($this->b / 0x11)
                    );
                }

                return null;
            case self::COLOR_HEX_6:
                return \sprintf('#%02X%02X%02X', $this->r, $this->g, $this->b);
            case self::COLOR_RGB:
                if (1.0 === $this->a) {
                    return \sprintf('rgb(%d, %d, %d)', $this->r, $this->g, $this->b);
                }

                return \sprintf('rgb(%d, %d, %d, %s)', $this->r, $this->g, $this->b, \round($this->a, 4));
            case self::COLOR_RGBA:
                return \sprintf('rgba(%d, %d, %d, %s)', $this->r, $this->g, $this->b, \round($this->a, 4));
            case self::COLOR_HSL:
                $val = self::rgbToHsl($this->r, $this->g, $this->b);
                if (1.0 === $this->a) {
                    return \vsprintf('hsl(%d, %d%%, %d%%)', $val);
                }

                return \sprintf('hsl(%d, %d%%, %d%%, %s)', $val[0], $val[1], $val[2], \round($this->a, 4));
            case self::COLOR_HSLA:
                $val = self::rgbToHsl($this->r, $this->g, $this->b);

                return \sprintf('hsla(%d, %d%%, %d%%, %s)', $val[0], $val[1], $val[2], \round($this->a, 4));
            case self::COLOR_HEX_4:
                if (0 === $this->r % 0x11 && 0 === $this->g % 0x11 && 0 === $this->b % 0x11 && 0 === ((int) ($this->a * 255)) % 0x11) {
                    return \sprintf(
                        '#%1X%1X%1X%1X',
                        \round($this->r / 0x11),
                        \round($this->g / 0x11),
                        \round($this->b / 0x11),
                        \round($this->a * 0xF)
                    );
                }

                return null;

            case self::COLOR_HEX_8:
                return \sprintf('#%02X%02X%02X%02X', $this->r, $this->g, $this->b, \round($this->a * 0xFF));
        }

        return null;
    }

    public function hasAlpha(int $variant = null): bool
    {
        if (null === $variant) {
            $variant = $this->variant;
        }

        switch ($variant) {
            case self::COLOR_NAME:
            case self::COLOR_RGB:
            case self::COLOR_HSL:
                return \abs($this->a - 1) >= 0.0001;
            case self::COLOR_RGBA:
            case self::COLOR_HSLA:
            case self::COLOR_HEX_4:
            case self::COLOR_HEX_8:
                return true;
            default:
                return false;
        }
    }

    protected function setValues(string $value): void
    {
        $value = \strtolower(\trim($value));
        // Find out which variant of color input it is
        if (isset(self::$color_map[$value])) {
            if (!$this->setValuesFromHex(self::$color_map[$value])) {
                return;
            }

            $variant = self::COLOR_NAME;
        } elseif ('#' === $value[0]) {
            $variant = $this->setValuesFromHex(\substr($value, 1));

            if (!$variant) {
                return;
            }
        } else {
            $variant = $this->setValuesFromFunction($value);

            if (!$variant) {
                return;
            }
        }

        // If something has gone horribly wrong
        if ($this->r > 0xFF || $this->g > 0xFF || $this->b > 0xFF || $this->a > 1) {
            $this->variant = null; // @codeCoverageIgnore
        } else {
            $this->variant = $variant;
            $this->r = (int) $this->r;
            $this->g = (int) $this->g;
            $this->b = (int) $this->b;
            $this->a = (float) $this->a;
        }
    }

    protected function setValuesFromHex(string $hex): ?int
    {
        if (!\ctype_xdigit($hex)) {
            return null;
        }

        switch (\strlen($hex)) {
            case 3:
                $variant = self::COLOR_HEX_3;
                break;
            case 6:
                $variant = self::COLOR_HEX_6;
                break;
            case 4:
                $variant = self::COLOR_HEX_4;
                break;
            case 8:
                $variant = self::COLOR_HEX_8;
                break;
            default:
                return null;
        }

        switch ($variant) {
            case self::COLOR_HEX_4:
                $this->a = \hexdec($hex[3]) / 0xF;
                // no break
            case self::COLOR_HEX_3:
                $this->r = \hexdec($hex[0]) * 0x11;
                $this->g = \hexdec($hex[1]) * 0x11;
                $this->b = \hexdec($hex[2]) * 0x11;
                break;
            case self::COLOR_HEX_8:
                $this->a = \hexdec(\substr($hex, 6, 2)) / 0xFF;
                // no break
            case self::COLOR_HEX_6:
                $hex = \str_split($hex, 2);
                $this->r = \hexdec($hex[0]);
                $this->g = \hexdec($hex[1]);
                $this->b = \hexdec($hex[2]);
                break;
        }

        return $variant;
    }

    protected function setValuesFromFunction(string $value): ?int
    {
        if (!\preg_match('/^((?:rgb|hsl)a?)\\s*\\(([0-9\\.%,\\s\\/\\-]+)\\)$/i', $value, $match)) {
            return null;
        }

        switch (\strtolower($match[1])) {
            case 'rgb':
                $variant = self::COLOR_RGB;
                break;
            case 'rgba':
                $variant = self::COLOR_RGBA;
                break;
            case 'hsl':
                $variant = self::COLOR_HSL;
                break;
            case 'hsla':
                $variant = self::COLOR_HSLA;
                break;
            default:
                return null; // @codeCoverageIgnore
        }

        $params = \preg_replace('/[,\\s\\/]+/', ',', \trim($match[2]));
        $params = \explode(',', $params);
        $params = \array_map('trim', $params);

        if (\count($params) < 3 || \count($params) > 4) {
            return null;
        }

        foreach ($params as $i => &$color) {
            if (false !== \strpos($color, '%')) {
                $color = (float) \str_replace('%', '', $color);

                if (3 === $i) {
                    $color = $color / 100;
                } elseif (\in_array($variant, [self::COLOR_RGB, self::COLOR_RGBA], true)) {
                    $color = \round($color / 100 * 0xFF);
                }
            }

            $color = (float) $color;

            if (0 === $i && \in_array($variant, [self::COLOR_HSL, self::COLOR_HSLA], true)) {
                $color = \fmod(\fmod($color, 360) + 360, 360);
            }
        }

        /** @psalm-var non-empty-array<array-key, float> $params Psalm bug workaround */
        switch ($variant) {
            case self::COLOR_RGBA:
            case self::COLOR_RGB:
                if (\min($params) < 0 || \max($params) > 0xFF) {
                    return null;
                }
                break;
            case self::COLOR_HSLA:
            case self::COLOR_HSL:
                if (\min($params) < 0 || $params[0] > 360 || \max($params[1], $params[2]) > 100) {
                    return null;
                }
                break;
        }

        if (4 === \count($params)) {
            if ($params[3] > 1) {
                return null;
            }

            $this->a = $params[3];
        }

        if (self::COLOR_HSLA === $variant || self::COLOR_HSL === $variant) {
            $params = self::hslToRgb($params[0], $params[1], $params[2]);
        }

        [$this->r, $this->g, $this->b] = $params;

        return $variant;
    }

    /**
     * Turns HSL color to RGB. Black magic.
     *
     * @param float $h Hue
     * @param float $s Saturation
     * @param float $l Lightness
     *
     * @return int[] RGB array
     */
    public static function hslToRgb(float $h, float $s, float $l): array
    {
        if (\min($h, $s, $l) < 0) {
            throw new InvalidArgumentException('The parameters for hslToRgb should be no less than 0');
        }

        if ($h > 360 || \max($s, $l) > 100) {
            throw new InvalidArgumentException('The parameters for hslToRgb should be no more than 360, 100, and 100 respectively');
        }

        $h /= 360;
        $s /= 100;
        $l /= 100;

        $m2 = ($l <= 0.5) ? $l * ($s + 1) : $l + $s - $l * $s;
        $m1 = $l * 2 - $m2;

        return [
            (int) \round(self::hueToRgb($m1, $m2, $h + 1 / 3) * 0xFF),
            (int) \round(self::hueToRgb($m1, $m2, $h) * 0xFF),
            (int) \round(self::hueToRgb($m1, $m2, $h - 1 / 3) * 0xFF),
        ];
    }

    /**
     * Converts RGB to HSL. Color inversion of previous black magic is white magic?
     *
     * @param float $red   Red
     * @param float $green Green
     * @param float $blue  Blue
     *
     * @return float[] HSL array
     */
    public static function rgbToHsl(float $red, float $green, float $blue): array
    {
        if (\min($red, $green, $blue) < 0) {
            throw new InvalidArgumentException('The parameters for rgbToHsl should be no less than 0');
        }

        if (\max($red, $green, $blue) > 0xFF) {
            throw new InvalidArgumentException('The parameters for rgbToHsl should be no more than 255');
        }

        $clrMin = \min($red, $green, $blue);
        $clrMax = \max($red, $green, $blue);
        $deltaMax = $clrMax - $clrMin;

        $L = ($clrMax + $clrMin) / 510;

        if (0 == $deltaMax) {
            $H = 0.0;
            $S = 0.0;
        } else {
            if (0.5 > $L) {
                $S = $deltaMax / ($clrMax + $clrMin);
            } else {
                $S = $deltaMax / (510 - $clrMax - $clrMin);
            }

            if ($clrMax === $red) {
                $H = ($green - $blue) / (6.0 * $deltaMax);

                if (0 > $H) {
                    $H += 1.0;
                }
            } elseif ($clrMax === $green) {
                $H = 1 / 3 + ($blue - $red) / (6.0 * $deltaMax);
            } else {
                $H = 2 / 3 + ($red - $green) / (6.0 * $deltaMax);
            }
        }

        return [
            \fmod($H * 360, 360),
            $S * 100,
            $L * 100,
        ];
    }

    /**
     * Helper function for hslToRgb. Even blacker magic.
     *
     * @return float Color value
     */
    private static function hueToRgb(float $m1, float $m2, float $hue): float
    {
        $hue = ($hue < 0) ? $hue + 1 : (($hue > 1) ? $hue - 1 : $hue);
        if ($hue * 6 < 1) {
            return $m1 + ($m2 - $m1) * $hue * 6;
        }
        if ($hue * 2 < 1) {
            return $m2;
        }
        if ($hue * 3 < 2) {
            return $m1 + ($m2 - $m1) * (2 / 3 - $hue) * 6;
        }

        return $m1;
    }
}
