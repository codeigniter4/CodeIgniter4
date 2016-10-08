<?php namespace CodeIgniter\HTTP;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Class HeaderTrait
 *
 * Provides common functionality for including and working with
 * headers in any class. Used internally by CodeIgniter\Mail\Message
 * and CodeIgniter\HTTP\Message classes.
 *
 * @package CodeIgniter\HTTP
 */
trait HeaderTrait
{
    /**
     * List of all HTTP request headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Holds a map of lower-case header names
     * and their normal-case key as it is in $headers.
     * Used for case-insensitive header access.
     *
     * @var array
     */
    protected $headerMap = [];

    //--------------------------------------------------------------------

    /**
     * Returns an array containing all headers.
     *
     * @return array        An array of the request headers
     */
    public function getHeaders(): array
    {
        // If no headers are defined, but the user is
        // requesting it, then it's likely they want
        // it to be populated so do that...
        if (empty($this->headers) && method_exists($this, 'populateHeaders'))
        {
            $this->populateHeaders();
        }

        return $this->headers;
    }

    //--------------------------------------------------------------------

    /**
     * Returns a single header object. If multiple headers with the same
     * name exist, then will return an array of header objects.
     *
     * @param      $name
     * @param null $filter
     *
     * @return array|\CodeIgniter\HTTP\Header
     */
    public function getHeader($name)
    {
        $orig_name = $this->getHeaderName($name);

        if ( ! isset($this->headers[$orig_name]))
        {
            return NULL;
        }

        return $this->headers[$orig_name];
    }

    //--------------------------------------------------------------------

    /**
     * Determines whether a header exists.
     *
     * @param $name
     *
     * @return bool
     */
    public function hasHeader($name): bool
    {
        $orig_name = $this->getHeaderName($name);

        return isset($this->headers[$orig_name]);
    }

    //--------------------------------------------------------------------


    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        $orig_name = $this->getHeaderName($name);

        if (! array_key_exists($orig_name, $this->headers))
        {
            return '';
        }

        // If there are more than 1 headers with this name,
        // then return the value of the first.
        if (is_array($this->headers[$orig_name]))
        {
            return $this->headers[$orig_name][0]->getValueLine();
        }

        return $this->headers[$orig_name]->getValueLine();
    }

    //--------------------------------------------------------------------


    /**
     * Sets a header and it's value.
     *
     * @param string $name
     * @param        $value
     * @param bool   $spaceParts Whether a space should be placed after colon in full header
     *
     * @return Message
     */
    public function setHeader(string $name, $value, $spaceParts=true)
    {
        if (! isset($this->headers[$name]))
        {
            $this->headers[$name] = new Header($name, $value, $spaceParts);

            $this->headerMap[strtolower($name)] = $name;

            return $this;
        }

        if (! is_array($this->headers[$name]))
        {
            $this->headers[$name] = [$this->headers[$name]];
        }

        $this->headers[$name][] = new Header($name, $value, $spaceParts);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Removes a header from the list of headers we track.
     *
     * @param string $name
     *
     * @return Message
     */
    public function removeHeader(string $name): self
    {
        $orig_name = $this->getHeaderName($name);

        unset($this->headers[$orig_name]);
        unset($this->headerMap[strtolower($name)]);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Adds an additional header value to any headers that accept
     * multiple values (i.e. are an array or implement ArrayAccess)
     *
     * @param string $name
     * @param        $value
     *
     * @return string
     */
    public function appendHeader(string $name, $value): self
    {
        $orig_name = $this->getHeaderName($name);

        $this->headers[$orig_name]->appendValue($value);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Adds an additional header value to any headers that accept
     * multiple values (i.e. are an array or implement ArrayAccess)
     *
     * @param string $name
     * @param        $value
     *
     * @return string
     */
    public function prependHeader(string $name, $value): self
    {
        $orig_name = $this->getHeaderName($name);

        $this->headers[$orig_name]->prependValue($value);

        return $this;
    }

    //--------------------------------------------------------------------

    /**
     * Takes a header name in any case, and returns the
     * normal-case version of the header.
     *
     * @param $name
     *
     * @return string
     */
    protected function getHeaderName($name): string
    {
        $lower_name = strtolower($name);

        return isset($this->headerMap[$lower_name]) ? $this->headerMap[$lower_name] : $name;
    }

    //--------------------------------------------------------------------
}

