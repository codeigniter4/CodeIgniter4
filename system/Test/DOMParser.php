<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Test;

use BadMethodCallException;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;

/**
 * Load a response into a DOMDocument for testing assertions based on that
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
		if (! extension_loaded('DOM'))
		{
			// always there in travis-ci
			// @codeCoverageIgnoreStart
			throw new BadMethodCallException('DOM extension is required, but not currently loaded.');
			// @codeCoverageIgnoreEnd
		}

		$this->dom = new DOMDocument('1.0', 'utf-8');
	}

	/**
	 * Returns the body of the current document.
	 *
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->dom->saveHTML();
	}

	/**
	 * Sets a string as the body that we want to work with.
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function withString(string $content)
	{
		// converts all special characters to utf-8
		$content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

		//turning off some errors
		libxml_use_internal_errors(true);

		if (! $this->dom->loadHTML($content))
		{
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
	 * @param string $path
	 *
	 * @return DOMParser
	 */
	public function withFile(string $path)
	{
		if (! is_file($path))
		{
			throw new InvalidArgumentException(basename($path) . ' is not a valid file.');
		}

		$content = file_get_contents($path);

		return $this->withString($content);
	}

	/**
	 * Checks to see if the text is found within the result.
	 *
	 * @param string $search
	 * @param string $element
	 *
	 * @return boolean
	 */
	public function see(string $search = null, string $element = null): bool
	{
		// If Element is null, we're just scanning for text
		if (is_null($element))
		{
			$content = $this->dom->saveHTML($this->dom->documentElement);
			return mb_strpos($content, $search) !== false;
		}

		$result = $this->doXPath($search, $element);

		return (bool) $result->length;
	}

	/**
	 * Checks to see if the text is NOT found within the result.
	 *
	 * @param string      $search
	 * @param string|null $element
	 *
	 * @return boolean
	 */
	public function dontSee(string $search = null, string $element = null): bool
	{
		return ! $this->see($search, $element);
	}

	/**
	 * Checks to see if an element with the matching CSS specifier
	 * is found within the current DOM.
	 *
	 * @param string $element
	 *
	 * @return boolean
	 */
	public function seeElement(string $element): bool
	{
		return $this->see(null, $element);
	}

	/**
	 * Checks to see if the element is available within the result.
	 *
	 * @param string $element
	 *
	 * @return boolean
	 */
	public function dontSeeElement(string $element): bool
	{
		return $this->dontSee(null, $element);
	}

	/**
	 * Determines if a link with the specified text is found
	 * within the results.
	 *
	 * @param string      $text
	 * @param string|null $details
	 *
	 * @return boolean
	 */
	public function seeLink(string $text, string $details = null): bool
	{
		return $this->see($text, 'a' . $details);
	}

	/**
	 * Checks for an input named $field with a value of $value.
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return boolean
	 */
	public function seeInField(string $field, string $value): bool
	{
		$result = $this->doXPath(null, 'input', ["[@value=\"{$value}\"][@name=\"{$field}\"]"]);

		return (bool) $result->length;
	}

	/**
	 * Checks for checkboxes that are currently checked.
	 *
	 * @param string $element
	 *
	 * @return boolean
	 */
	public function seeCheckboxIsChecked(string $element): bool
	{
		$result = $this->doXPath(null, 'input' . $element, [
			'[@type="checkbox"]',
			'[@checked="checked"]',
		]);

		return (bool) $result->length;
	}

	//--------------------------------------------------------------------

	/**
	 * Search the DOM using an XPath expression.
	 *
	 * @param  string|null $search
	 * @param  string      $element
	 * @param  array       $paths
	 *
	 * @return DOMNodeList
	 */
	protected function doXPath(?string $search, string $element, array $paths = [])
	{
		// Otherwise, grab any elements that match
		// the selector
		$selector = $this->parseSelector($element);

		$path = '';

		// By ID
		if (! empty($selector['id']))
		{
			$path = empty($selector['tag'])
				? "id(\"{$selector['id']}\")"
				: "//{$selector['tag']}[@id=\"{$selector['id']}\"]";
		}
		// By Class
		elseif (! empty($selector['class']))
		{
			$path = empty($selector['tag'])
				? "//*[@class=\"{$selector['class']}\"]"
				: "//{$selector['tag']}[@class=\"{$selector['class']}\"]";
		}
		// By tag only
		elseif (! empty($selector['tag']))
		{
			$path = "//{$selector['tag']}";
		}

		if (! empty($selector['attr']))
		{
			foreach ($selector['attr'] as $key => $value)
			{
				$path .= "[@{$key}=\"{$value}\"]";
			}
		}

		// $paths might contain a number of different
		// ready to go xpath portions to tack on.
		if (! empty($paths) && is_array($paths))
		{
			foreach ($paths as $extra)
			{
				$path .= $extra;
			}
		}

		if (! is_null($search))
		{
			$path .= "[contains(., \"{$search}\")]";
		}

		$xpath = new DOMXPath($this->dom);

		return $xpath->query($path);
	}

	/**
	 * Look for the a selector  in the passed text.
	 *
	 * @param  string $selector
	 * @return array
	 */
	public function parseSelector(string $selector)
	{
		$tag   = null;
		$id    = null;
		$class = null;
		$attr  = null;

		// ID?
		if ($pos = strpos($selector, '#') !== false)
		{
			[$tag, $id] = explode('#', $selector);
		}
		// Attribute
		elseif (strpos($selector, '[') !== false && strpos($selector, ']') !== false)
		{
			$open  = strpos($selector, '[');
			$close = strpos($selector, ']');

			$tag  = substr($selector, 0, $open);
			$text = substr($selector, $open + 1, $close - 2);

			// We only support a single attribute currently
			$text = explode(',', $text);
			$text = trim(array_shift($text));

			[$name, $value] = explode('=', $text);
			$name               = trim($name);
			$value              = trim($value);
			$attr               = [$name => trim($value, '] ')];
		}
		// Class?
		elseif ($pos = strpos($selector, '.') !== false)
		{
			[$tag, $class] = explode('.', $selector);
		}
		// Otherwise, assume the entire string is our tag
		else
		{
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
