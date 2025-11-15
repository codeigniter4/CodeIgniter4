<?php

declare(strict_types=1);

namespace Laminas\Escaper;

use function assert;
use function bin2hex;
use function ctype_digit;
use function hexdec;
use function htmlspecialchars;
use function in_array;
use function is_string;
use function mb_convert_encoding;
use function ord;
use function preg_match;
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Context specific methods for use in secure output escaping
 *
 * @final
 */
class Escaper implements EscaperInterface
{
    /**
     * Entity Map mapping Unicode codepoints to any available named HTML entities.
     *
     * While HTML supports far more named entities, the lowest common denominator
     * has become HTML5's XML Serialisation which is restricted to the those named
     * entities that XML supports. Using HTML entities would result in this error:
     *     XML Parsing Error: undefined entity
     *
     * @var array<int, string>
     */
    protected static $htmlNamedEntityMap = [
        34 => 'quot', // quotation mark
        38 => 'amp', // ampersand
        60 => 'lt', // less-than sign
        62 => 'gt', // greater-than sign
    ];

    /**
     * Current encoding for escaping. If not UTF-8, we convert strings from this encoding
     * pre-escaping and back to this encoding post-escaping.
     *
     * @var non-empty-string
     */
    protected $encoding = 'utf-8';

    /**
     * Holds the value of the special flags passed as second parameter to
     * htmlspecialchars().
     *
     * @var int
     */
    protected $htmlSpecialCharsFlags;

    /**
     * Static Matcher which escapes characters for HTML Attribute contexts
     *
     * @var callable
     * @psalm-var callable(array<array-key, string>):string
     */
    protected $htmlAttrMatcher;

    /**
     * Static Matcher which escapes characters for Javascript contexts
     *
     * @var callable
     * @psalm-var callable(array<array-key, string>):string
     */
    protected $jsMatcher;

    /**
     * Static Matcher which escapes characters for CSS Attribute contexts
     *
     * @var callable
     * @psalm-var callable(array<array-key, string>):string
     */
    protected $cssMatcher;

    /**
     * List of all encoding supported by this class
     *
     * @var list<non-empty-string>
     */
    protected $supportedEncodings = [
        'iso-8859-1',
        'iso8859-1',
        'iso-8859-5',
        'iso8859-5',
        'iso-8859-15',
        'iso8859-15',
        'utf-8',
        'cp866',
        'ibm866',
        '866',
        'cp1251',
        'windows-1251',
        'win-1251',
        '1251',
        'cp1252',
        'windows-1252',
        '1252',
        'koi8-r',
        'koi8-ru',
        'koi8r',
        'big5',
        '950',
        'gb2312',
        '936',
        'big5-hkscs',
        'shift_jis',
        'sjis',
        'sjis-win',
        'cp932',
        '932',
        'euc-jp',
        'eucjp',
        'eucjp-win',
        'macroman',
    ];

    /**
     * Constructor: Single parameter allows setting of global encoding for use by
     * the current object.
     *
     * @param non-empty-string|null $encoding
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(?string $encoding = null)
    {
        if ($encoding !== null) {
            if ($encoding === '') {
                throw new Exception\InvalidArgumentException(
                    static::class . ' constructor parameter does not allow a blank value'
                );
            }

            $encoding = strtolower($encoding);
            if (! in_array($encoding, $this->supportedEncodings)) {
                throw new Exception\InvalidArgumentException(
                    'Value of \'' . $encoding . '\' passed to ' . static::class
                    . ' constructor parameter is invalid. Provide an encoding supported by htmlspecialchars()'
                );
            }

            $this->encoding = $encoding;
        }

        // We take advantage of ENT_SUBSTITUTE flag to correctly deal with invalid UTF-8 sequences.
        $this->htmlSpecialCharsFlags = ENT_QUOTES | ENT_SUBSTITUTE;

        // set matcher callbacks
        $this->htmlAttrMatcher =
            /** @param array<array-key, string> $matches */
            fn(array $matches): string => $this->htmlAttrMatcher($matches);
        $this->jsMatcher       =
            /** @param array<array-key, string> $matches */
            fn(array $matches): string => $this->jsMatcher($matches);
        $this->cssMatcher      =
            /** @param array<array-key, string> $matches */
            fn(array $matches): string => $this->cssMatcher($matches);
    }

    /**
     * Return the encoding that all output/input is expected to be encoded in.
     *
     * @return non-empty-string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /** @inheritDoc */
    public function escapeHtml(string $string)
    {
        return htmlspecialchars($string, $this->htmlSpecialCharsFlags, $this->encoding);
    }

    /** @inheritDoc */
    public function escapeHtmlAttr(string $string)
    {
        $string = $this->toUtf8($string);
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        $result = preg_replace_callback('/[^a-z0-9,\.\-_]/iSu', $this->htmlAttrMatcher, $string);
        assert(is_string($result));

        return $this->fromUtf8($result);
    }

    /** @inheritDoc */
    public function escapeJs(string $string)
    {
        $string = $this->toUtf8($string);
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        $result = preg_replace_callback('/[^a-z0-9,\._]/iSu', $this->jsMatcher, $string);
        assert(is_string($result));

        return $this->fromUtf8($result);
    }

    /** @inheritDoc */
    public function escapeUrl(string $string)
    {
        return rawurlencode($string);
    }

    /** @inheritDoc */
    public function escapeCss(string $string)
    {
        $string = $this->toUtf8($string);
        if ($string === '' || ctype_digit($string)) {
            return $string;
        }

        $result = preg_replace_callback('/[^a-z0-9]/iSu', $this->cssMatcher, $string);
        assert(is_string($result));

        return $this->fromUtf8($result);
    }

    /**
     * Callback function for preg_replace_callback that applies HTML Attribute
     * escaping to all matches.
     *
     * @param array<array-key, string> $matches
     * @return string
     */
    protected function htmlAttrMatcher($matches)
    {
        $chr = $matches[0];
        $ord = ord($chr);

        /**
         * The following replaces characters undefined in HTML with the
         * hex entity for the Unicode replacement character.
         */
        if (
            ($ord <= 0x1f && $chr !== "\t" && $chr !== "\n" && $chr !== "\r")
            || ($ord >= 0x7f && $ord <= 0x9f)
        ) {
            return '&#xFFFD;';
        }

        /**
         * Check if the current character to escape has a name entity we should
         * replace it with while grabbing the integer value of the character.
         */
        if (strlen($chr) > 1) {
            $chr = $this->convertEncoding($chr, 'UTF-32BE', 'UTF-8');
        }

        $hex = bin2hex($chr);
        $ord = hexdec($hex);
        if (isset(static::$htmlNamedEntityMap[$ord])) {
            return '&' . static::$htmlNamedEntityMap[$ord] . ';';
        }

        /**
         * Per OWASP recommendations, we'll use upper hex entities
         * for any other characters where a named entity does not exist.
         */
        if ($ord > 255) {
            return sprintf('&#x%04X;', $ord);
        }
        return sprintf('&#x%02X;', $ord);
    }

    /**
     * Callback function for preg_replace_callback that applies Javascript
     * escaping to all matches.
     *
     * @param array<array-key, string> $matches
     * @return string
     */
    protected function jsMatcher($matches)
    {
        $chr = $matches[0];
        if (strlen($chr) === 1) {
            return sprintf('\\x%02X', ord($chr));
        }
        $chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
        $hex = strtoupper(bin2hex($chr));
        if (strlen($hex) <= 4) {
            return sprintf('\\u%04s', $hex);
        }
        $highSurrogate = substr($hex, 0, 4);
        $lowSurrogate  = substr($hex, 4, 4);
        return sprintf('\\u%04s\\u%04s', $highSurrogate, $lowSurrogate);
    }

    /**
     * Callback function for preg_replace_callback that applies CSS
     * escaping to all matches.
     *
     * @param array<array-key, string> $matches
     * @return string
     */
    protected function cssMatcher($matches)
    {
        $chr = $matches[0];
        if (strlen($chr) === 1) {
            $ord = ord($chr);
        } else {
            $chr = $this->convertEncoding($chr, 'UTF-32BE', 'UTF-8');
            $ord = hexdec(bin2hex($chr));
        }
        return sprintf('\\%X ', $ord);
    }

    /**
     * Converts a string to UTF-8 from the base encoding. The base encoding is set via this
     *
     * @param string $string
     * @throws Exception\RuntimeException
     * @return string
     */
    protected function toUtf8($string)
    {
        if ($this->getEncoding() === 'utf-8') {
            $result = $string;
        } else {
            $result = $this->convertEncoding($string, 'UTF-8', $this->getEncoding());
        }

        if (! $this->isUtf8($result)) {
            throw new Exception\RuntimeException(
                sprintf('String to be escaped was not valid UTF-8 or could not be converted: %s', $result)
            );
        }

        return $result;
    }

    /**
     * Converts a string from UTF-8 to the base encoding. The base encoding is set via this
     *
     * @param string $string
     * @return string
     */
    protected function fromUtf8($string)
    {
        if ($this->getEncoding() === 'utf-8') {
            return $string;
        }

        return $this->convertEncoding($string, $this->getEncoding(), 'UTF-8');
    }

    /**
     * Checks if a given string appears to be valid UTF-8 or not.
     *
     * @param string $string
     * @return bool
     */
    protected function isUtf8($string)
    {
        return $string === '' || preg_match('/^./su', $string);
    }

    /**
     * Encoding conversion helper which wraps mb_convert_encoding
     *
     * @param string $string
     * @param string $to
     * @param array|string $from
     * @return string
     */
    protected function convertEncoding($string, $to, $from)
    {
        $result = mb_convert_encoding($string, $to, $from);

        if ($result === false) {
            return ''; // return non-fatal blank string on encoding errors from users
        }

        return $result;
    }
}
