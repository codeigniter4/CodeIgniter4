<?php namespace Tests\Support\DOM;

class DOMParser
{
	/**
	 * @var \DOMDocument
	 */
	protected $dom;

	public function __construct()
	{
		if (! extension_loaded('DOM'))
		{
			throw new \BadMethodCallException('DOM extension is required, but not currently loaded.');
		}

		$this->dom = new \DOMDocument('1.0', 'utf-8');
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
			throw new \BadMethodCallException('Invalid HTML');
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
	 * @return \Tests\Support\DOM\DOMParser
	 */
	public function withFile(string $path)
	{
		if (! is_file($path))
		{
			throw new \InvalidArgumentException(basename($path).' is not a valid file.');
		}

		$content = file_get_contents($path);

		return $this->withString($content);
	}

	/**
	 * Checks to see if the text is found within the result.
	 *
	 * @param string $search
	 *
	 * @return bool
	 */
	public function see(string $search, string $element=null): bool
	{
		// If Element is null, we're just scanning for text
		if (is_null($element))
		{
			$content = $this->dom->saveHTML();
			return strpos($content, $search) !== false;
		}

		// Otherwise, grab any elements that match
		// the selector
		$selector = $this->parseSelector($element);

		$path = '';

		// By ID
		if (! empty($selector['id']))
		{
			$path = empty($selector['tag'])
				? "id(\"{$selector['id']}\")"
				: "//body//{$selector['tag']}[@id=\"{$selector['id']}\"]";
		}
		// By Class
		else if (! empty($selector['class']))
		{
			$path = empty($selector['tag'])
				? "//*[@class=\"{$selector['class']}\"]"
				: "//body//{$selector['tag']}[@class=\"{$selector['class']}\"]";
		}
		// By tag only
		else if (! empty($selector['tag']))
		{
			$path = "//body//{$selector['tag']}";
		}

		$path .= "[contains(., \"{$search}\")]";

		$xpath = new \DOMXPath($this->dom);

		$result = $xpath->query($path);

		return (bool)$result->length;
	}

	/**
	 * Checks to see if the text is NOT found within the result.
	 *
	 * @param string      $search
	 * @param string|null $element
	 *
	 * @return bool
	 */
	public function dontSee(string $search, string $element=null): bool
	{
		return ! $this->see($search, $element);
	}

	public function parseSelector(string $selector)
	{
		$tag   = null;
		$id    = null;
		$class = null;
		$attr  = null;

		// ID?
		if ($pos = strpos($selector, '#') !== false)
		{
			list($tag, $id) = explode('#', $selector);
		}
		// Attribute
		elseif (strpos($selector, '[') !== false && strpos($selector, ']') !== false)
		{
			$open = strpos($selector, '[');
			$close = strpos($selector, ']');

			$tag = substr($selector, 0, $open);
			$text = substr($selector, $open+1, $close-2);

			// We only support a single attribute currently
			$text = explode(',', $text);
			$text = trim(array_shift($text));

			list($name, $value) = explode('=', $text);
			$name = trim($name);
			$value = trim($value);
			$attr = [$name => $value];
		}
		// Class?
		elseif ($pos = strpos($selector, '.') !== false)
		{
			list($tag, $class) = explode('.', $selector);
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
			'attr'  => $attr
		];
	}

}
