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

namespace Kint\Zval;

class BlobValue extends Value
{
    /**
     * @var array Character encodings to detect
     *
     * @see https://secure.php.net/function.mb-detect-order
     *
     * In practice, mb_detect_encoding can only successfully determine the
     * difference between the following common charsets at once without
     * breaking things for one of the other charsets:
     * - ASCII
     * - UTF-8
     * - SJIS
     * - EUC-JP
     *
     * The order of the charsets is significant. If you put UTF-8 before ASCII
     * it will never match ASCII, because UTF-8 is a superset of ASCII.
     * Similarly, SJIS and EUC-JP frequently match UTF-8 strings, so you should
     * check UTF-8 first. SJIS and EUC-JP seem to work either way, but SJIS is
     * more common so it should probably be first.
     *
     * While you're free to experiment with other charsets, remember to keep
     * this behavior in mind when setting up your char_encodings array.
     *
     * This depends on the mbstring extension
     */
    public static $char_encodings = [
        'ASCII',
        'UTF-8',
    ];

    /**
     * @var array Legacy character encodings to detect
     *
     * @see https://secure.php.net/function.iconv
     *
     * Assuming the other encoding checks fail, this will perform a
     * simple iconv conversion to check for invalid bytes. If any are
     * found it will not match.
     *
     * This can be useful for ambiguous single byte encodings like
     * windows-125x and iso-8859-x which have practically undetectable
     * differences because they use every single byte available.
     *
     * This is *NOT* reliable and should not be trusted implicitly. As
     * with char_encodings, the order of the charsets is significant.
     *
     * This depends on the iconv extension
     */
    public static $legacy_encodings = [];

    public $type = 'string';
    public $encoding = false;
    public $hints = ['string'];

    public function getType()
    {
        if (false === $this->encoding) {
            return 'binary '.$this->type;
        }

        if ('ASCII' === $this->encoding) {
            return $this->type;
        }

        return $this->encoding.' '.$this->type;
    }

    public function getValueShort()
    {
        if ($rep = $this->value) {
            return '"'.$rep->contents.'"';
        }
    }

    public function transplant(Value $old)
    {
        parent::transplant($old);

        if ($old instanceof self) {
            $this->encoding = $old->encoding;
        }
    }

    public static function strlen($string, $encoding = false)
    {
        if (\function_exists('mb_strlen')) {
            if (false === $encoding) {
                $encoding = self::detectEncoding($string);
            }

            if ($encoding && 'ASCII' !== $encoding) {
                return \mb_strlen($string, $encoding);
            }
        }

        return \strlen($string);
    }

    public static function substr($string, $start, $length = null, $encoding = false)
    {
        if (\function_exists('mb_substr')) {
            if (false === $encoding) {
                $encoding = self::detectEncoding($string);
            }

            if ($encoding && 'ASCII' !== $encoding) {
                return \mb_substr($string, $start, $length, $encoding);
            }
        }

        // Special case for substr/mb_substr discrepancy
        if ('' === $string) {
            return '';
        }

        return \substr($string, $start, isset($length) ? $length : PHP_INT_MAX);
    }

    public static function detectEncoding($string)
    {
        if (\function_exists('mb_detect_encoding')) {
            if ($ret = \mb_detect_encoding($string, self::$char_encodings, true)) {
                return $ret;
            }
        }

        // Pretty much every character encoding uses first 32 bytes as control
        // characters. If it's not a multi-byte format it's safe to say matching
        // any control character besides tab, nl, and cr means it's binary.
        if (\preg_match('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', $string)) {
            return false;
        }

        if (\function_exists('iconv')) {
            foreach (self::$legacy_encodings as $encoding) {
                if (@\iconv($encoding, $encoding, $string) === $string) {
                    return $encoding;
                }
            }
        } elseif (!\function_exists('mb_detect_encoding')) { // @codeCoverageIgnore
            // If a user has neither mb_detect_encoding, nor iconv, nor the
            // polyfills, there's not much we can do about it...
            // Pretend it's ASCII and pray the browser renders it properly.
            return 'ASCII'; // @codeCoverageIgnore
        }

        return false;
    }
}
