<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use InvalidArgumentException;
use Stringable;

/**
 * Class Header
 *
 * Represents a single HTTP header.
 *
 * @see \CodeIgniter\HTTP\HeaderTest
 */
class Header implements Stringable
{
    /**
     * The name of the header.
     *
     * @var string
     */
    protected $name;

    /**
     * The value of the header. May have more than one
     * value. If so, will be an array of strings.
     * E.g.,
     *   [
     *       'foo',
     *       [
     *           'bar' => 'fizz',
     *       ],
     *       'baz' => 'buzz',
     *   ]
     *
     * @var array<int|string, array<string, string>|string>|string
     */
    protected $value;

    /**
     * Header constructor. name is mandatory, if a value is provided, it will be set.
     *
     * @param array<int|string, array<string, string>|string>|string|null $value
     */
    public function __construct(string $name, $value = null)
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Returns the name of the header, in the same case it was set.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the raw value of the header. This may return either a string
     * or an array, depending on whether the header has multiple values or not.
     *
     * @return array<int|string, array<string, string>|string>|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the name of the header, overwriting any previous value.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setName(string $name)
    {
        $this->validateName($name);
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the value of the header, overwriting any previous value(s).
     *
     * @param array<int|string, array<string, string>|string>|string|null $value
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setValue($value = null)
    {
        $value = is_array($value) ? $value : (string) $value;

        $this->validateValue($value);

        $this->value = $value;

        return $this;
    }

    /**
     * Appends a value to the list of values for this header. If the
     * header is a single value string, it will be converted to an array.
     *
     * @param array<string, string>|string|null $value
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function appendValue($value = null)
    {
        if ($value === null) {
            return $this;
        }

        $this->validateValue($value);

        if (! is_array($this->value)) {
            $this->value = [$this->value];
        }

        if (! in_array($value, $this->value, true)) {
            $this->value[] = is_array($value) ? $value : (string) $value;
        }

        return $this;
    }

    /**
     * Prepends a value to the list of values for this header. If the
     * header is a single value string, it will be converted to an array.
     *
     * @param array<string, string>|string|null $value
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function prependValue($value = null)
    {
        if ($value === null) {
            return $this;
        }

        $this->validateValue($value);

        if (! is_array($this->value)) {
            $this->value = [$this->value];
        }

        array_unshift($this->value, $value);

        return $this;
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
     */
    public function getValueLine(): string
    {
        if (is_string($this->value)) {
            return $this->value;
        }
        if (! is_array($this->value)) {
            return '';
        }

        $options = [];

        foreach ($this->value as $key => $value) {
            if (is_string($key) && ! is_array($value)) {
                $options[] = $key . '=' . $value;
            } elseif (is_array($value)) {
                $key       = key($value);
                $options[] = $key . '=' . $value[$key];
            } elseif (is_numeric($key)) {
                $options[] = $value;
            }
        }

        return implode(', ', $options);
    }

    /**
     * Returns a representation of the entire header string, including
     * the header name and all values converted to the proper format.
     */
    public function __toString(): string
    {
        return $this->name . ': ' . $this->getValueLine();
    }

    /**
     * Validate header name.
     *
     * Regex is based on code from a guzzlehttp/psr7 library.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2
     *
     * @throws InvalidArgumentException
     */
    private function validateName(string $name): void
    {
        if (preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/D', $name) !== 1) {
            throw new InvalidArgumentException('The header name is not valid as per RFC 7230.');
        }
    }

    /**
     * Validate header value.
     *
     * Regex is based on code from a guzzlehttp/psr7 library.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7230#section-3.2
     *
     * @param array<int|string, array<string, string>|string>|int|string $value
     *
     * @throws InvalidArgumentException
     */
    private function validateValue(array|int|string $value): void
    {
        if (is_int($value)) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $this->validateValue($key);
                $this->validateValue($val);
            }

            return;
        }

        // The regular expression excludes obs-fold per RFC 7230#3.2.4, as sending folded lines
        // is deprecated and rare. This obscure HTTP/1.1 feature is unlikely to impact legitimate
        // use cases. Libraries like Guzzle and AMPHP follow the same principle.
        if (preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/D', $value) !== 1) {
            throw new InvalidArgumentException('The header value is not valid as per RFC 7230.');
        }
    }
}
