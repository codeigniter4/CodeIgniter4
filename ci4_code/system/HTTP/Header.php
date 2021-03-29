<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

/**
 * Class Header
 *
 * Represents a single HTTP header.
 */
class Header
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
	 *
	 * @var string|array
	 */
	protected $value;

	//--------------------------------------------------------------------

	/**
	 * Header constructor. name is mandatory, if a value is provided, it will be set.
	 *
	 * @param string            $name
	 * @param string|array|null $value
	 */
	public function __construct(string $name, $value = null)
	{
		$this->name = $name;
		$this->setValue($value);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the name of the header, in the same case it was set.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the raw value of the header. This may return either a string
	 * of an array, depending on whether the header has multiple values or not.
	 *
	 * @return array|string
	 */
	public function getValue()
	{
		return $this->value;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the name of the header, overwriting any previous value.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName(string $name)
	{
		$this->name = $name;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the value of the header, overwriting any previous value(s).
	 *
	 * @param string|array|null $value
	 *
	 * @return $this
	 */
	public function setValue($value = null)
	{
		$this->value = $value ?? '';

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Appends a value to the list of values for this header. If the
	 * header is a single value string, it will be converted to an array.
	 *
	 * @param string|array|null $value
	 *
	 * @return $this
	 */
	public function appendValue($value = null)
	{
		if ($value === null)
		{
			return $this;
		}

		if (! is_array($this->value))
		{
			$this->value = [$this->value];
		}

		if (! in_array($value, $this->value, true))
		{
			$this->value[] = $value;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Prepends a value to the list of values for this header. If the
	 * header is a single value string, it will be converted to an array.
	 *
	 * @param string|array|null $value
	 *
	 * @return $this
	 */
	public function prependValue($value = null)
	{
		if ($value === null)
		{
			return $this;
		}

		if (! is_array($this->value))
		{
			$this->value = [$this->value];
		}

		array_unshift($this->value, $value);

		return $this;
	}

	//--------------------------------------------------------------------

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
		if (is_string($this->value))
		{
			return $this->value;
		}
		if (! is_array($this->value))
		{
			return '';
		}

		$options = [];

		foreach ($this->value as $key => $value)
		{
			if (is_string($key) && ! is_array($value))
			{
				$options[] = $key . '=' . $value;
			}
			elseif (is_array($value))
			{
				$key       = key($value);
				$options[] = $key . '=' . $value[$key];
			}
			elseif (is_numeric($key))
			{
				$options[] = $value;
			}
		}

		return implode(', ', $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a representation of the entire header string, including
	 * the header name and all values converted to the proper format.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->name . ': ' . $this->getValueLine();
	}

	//--------------------------------------------------------------------
}
