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

namespace CodeIgniter\Test;

use CodeIgniter\Exceptions\BadMethodCallException;
use CodeIgniter\Exceptions\InvalidArgumentException;
use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Load a response into a DOMDocument for testing assertions based on that
 *
 * @see \CodeIgniter\Test\DOMParserTest
 */
class DOMParser
{
    /**
     * DOM for the body,
     *
     * @var DOMDocument
     */
    protected $dom;

    /**
     * Constructor.
     *
     * @throws BadMethodCallException
     */
    public function __construct()
    {
        if (! extension_loaded('DOM')) {
            throw new BadMethodCallException('DOM extension is required, but not currently loaded.'); // @codeCoverageIgnore
        }

        $this->dom = new DOMDocument('1.0', 'utf-8');
    }

    /**
     * Returns the body of the current document.
     */
    public function getBody(): string
    {
        return $this->dom->saveHTML();
    }

    /**
     * Sets a string as the body that we want to work with.
     *
     * @return $this
     */
    public function withString(string $content)
    {
        // DOMDocument::loadHTML() will treat your string as being in ISO-8859-1
        // (the HTTP/1.1 default character set) unless you tell it otherwise.
        // https://stackoverflow.com/a/8218649
        // So encode characters to HTML numeric string references.
        $content = mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');

        // turning off some errors
        libxml_use_internal_errors(true);

        if (! $this->dom->loadHTML($content)) {
            // unclear how we would get here, given that we are trapping libxml errors
            // @codeCoverageIgnoreStart
            libxml_clear_errors();

            throw new BadMethodCallException('Invalid HTML');
            // @codeCoverageIgnoreEnd
        }

        // ignore the whitespace.
        $this->dom->preserveWhiteSpace = false;

        return $this;
    }

    /**
     * Loads the contents of a file as a string
     * so that we can work with it.
     *
     * @return $this
     */
    public function withFile(string $path)
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException(basename($path) . ' is not a valid file.');
        }

        $content = file_get_contents($path);

        return $this->withString($content);
    }

    /**
     * Checks to see if the text is found within the result.
     */
    public function see(?string $search = null, ?string $element = null): bool
    {
        // If Element is null, we're just scanning for text
        if ($element === null) {
            $content = $this->dom->saveHTML($this->dom->documentElement);

            return mb_strpos($content, $search) !== false;
        }

        $result = $this->doXPath($search, $element);

        return (bool) $result->length;
    }

    /**
     * Checks to see if the text is NOT found within the result.
     */
    public function dontSee(?string $search = null, ?string $element = null): bool
    {
        return ! $this->see($search, $element);
    }

    /**
     * Checks to see if an element with the matching CSS specifier
     * is found within the current DOM.
     */
    public function seeElement(string $element): bool
    {
        return $this->see(null, $element);
    }

    /**
     * Checks to see if the element is available within the result.
     */
    public function dontSeeElement(string $element): bool
    {
        return $this->dontSee(null, $element);
    }

    /**
     * Determines if a link with the specified text is found
     * within the results.
     */
    public function seeLink(string $text, ?string $details = null): bool
    {
        return $this->see($text, 'a' . $details);
    }

    /**
     * Checks for an input named $field with a value of $value.
     */
    public function seeInField(string $field, string $value): bool
    {
        $result = $this->doXPath(null, 'input', ["[@value=\"{$value}\"][@name=\"{$field}\"]"]);

        return (bool) $result->length;
    }

    /**
     * Checks for checkboxes that are currently checked.
     */
    public function seeCheckboxIsChecked(string $element): bool
    {
        $result = $this->doXPath(null, 'input' . $element, [
            '[@type="checkbox"]',
            '[@checked="checked"]',
        ]);

        return (bool) $result->length;
    }

    /**
     * Checks to see if the XPath can be found.
     */
    public function seeXPath(string $path): bool
    {
        $xpath = new DOMXPath($this->dom);

        return (bool) $xpath->query($path)->length;
    }

    /**
     * Checks to see if the XPath can't be found.
     */
    public function dontSeeXPath(string $path): bool
    {
        return ! $this->seeXPath($path);
    }

    /**
     * Search the DOM using an XPath expression.
     *
     * @return DOMNodeList|false
     */
    protected function doXPath(?string $search, string $element, array $paths = [])
    {
        // Otherwise, grab any elements that match
        // the selector
        $selector = $this->parseSelector($element);

        $path = '';

        // By ID
        if (isset($selector['id'])) {
            $path = ($selector['tag'] === '')
                ? "id(\"{$selector['id']}\")"
                : "//{$selector['tag']}[@id=\"{$selector['id']}\"]";
        }
        // By Class
        elseif (isset($selector['class'])) {
            $path = ($selector['tag'] === '')
                ? "//*[@class=\"{$selector['class']}\"]"
                : "//{$selector['tag']}[@class=\"{$selector['class']}\"]";
        }
        // By tag only
        elseif ($selector['tag'] !== '') {
            $path = "//{$selector['tag']}";
        }

        if (isset($selector['attr'])) {
            foreach ($selector['attr'] as $key => $value) {
                $path .= "[@{$key}=\"{$value}\"]";
            }
        }

        // $paths might contain a number of different
        // ready to go xpath portions to tack on.
        foreach ($paths as $extra) {
            $path .= $extra;
        }

        if ($search !== null) {
            $path .= "[contains(., \"{$search}\")]";
        }

        $xpath = new DOMXPath($this->dom);

        return $xpath->query($path);
    }

    /**
     * Look for the a selector  in the passed text.
     *
     * @return array{tag: string, id: string|null, class: string|null, attr: array<string, string>|null}
     */
    public function parseSelector(string $selector)
    {
        $id    = null;
        $class = null;
        $attr  = null;

        // ID?
        if (str_contains($selector, '#')) {
            [$tag, $id] = explode('#', $selector);
        }
        // Attribute
        elseif (str_contains($selector, '[') && str_contains($selector, ']')) {
            $open  = strpos($selector, '[');
            $close = strpos($selector, ']');

            $tag  = substr($selector, 0, $open);
            $text = substr($selector, $open + 1, $close - 2);

            // We only support a single attribute currently
            $text = explode(',', $text);
            $text = trim(array_shift($text));

            [$name, $value] = explode('=', $text);

            $name  = trim($name);
            $value = trim($value);
            $attr  = [$name => trim($value, '] ')];
        }
        // Class?
        elseif (str_contains($selector, '.')) {
            [$tag, $class] = explode('.', $selector);
        }
        // Otherwise, assume the entire string is our tag
        else {
            $tag = $selector;
        }

        return [
            'tag'   => $tag,
            'id'    => $id,
            'class' => $class,
            'attr'  => $attr,
        ];
    }
}
