<?php namespace CodeIgniter\HTTP;

/**
 * Class MessageTrait
 *
 * Implements most of the methods required by the
 * MessageInterface.
 *
 * Used by Request and Response classes.
 *
 * @package CodeIgniter\HTTP
 */
trait MessageTrait
{
	/**
	 * The HTTP version.
	 *
	 * @var
	 */
	protected $protocolVersion;

	/**
	 * A HeaderCollection instance.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * The message body.
	 *
	 * @var
	 */
	protected $body;

	/**
	 * Is the message finalized?
	 *
	 * @var bool
	 */
	protected $isComplete = false;

	//--------------------------------------------------------------------

	/**
	 * Sets the value of the specified header.
	 *
	 * While header names are case-insensitive, the casing of the header will
	 * be preserved by this function, and returned from getHeaders().
	 *
	 * @param string          $name  Case-insensitive header field name.
	 * @param string|string[] $value Header value(s).
	 *
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function setHeader(string $name, $value): MessageInterface
	{
		if ($this->isComplete)
		{
			throw new \RuntimeException(\get_class($this).' instance already finalized');
		}

		$this->headers[$name] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a new value to an existing header.
	 *
	 * @param string $name  Case-insensitive header field name.
	 * @param        $value Header value.
	 *
	 * @return MessageInterface
	 */
	public function appendHeader(string $name, $value): MessageInterface
	{
		if ($this->isComplete)
		{
			throw new \RuntimeException(\get_class($this).' instance already finalized');
		}

		if ( ! \is_array($this->headers[$name]) && ! ($this->headers[$name] instanceof \ArrayAccess))
		{
			($this->headers[$name] instanceof HeaderInterface) && $name = $this->headers[$name]->getName();
			throw new \LogicException('Header "'.$name.'" does not support multiple values');
		}

		$this->headers[$name][] = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a header.
	 *
	 * @param string $name Case-insensitive header field name to remove.
	 *
	 * @return MessageInterface
	 */
	public function removeHeader(string $name): MessageInterface
	{
		if ($this->isComplete)
		{
			throw new \RuntimeException(\get_class($this).' instance already finalized');
		}

		unset($this->headers[$name]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks if a header exists by the given case-insensitive name.
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return bool Returns true if any header names match the given header
	 *     name using a case-insensitive string comparison. Returns false if
	 *     no matching header name is found in the message.
	 */
	public function hasHeader(string $name): bool
	{
		return array_key_exists($name, $this->headers);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves a message header value by the given case-insensitive name.
	 *
	 * This method returns an array of all the header values of the given
	 * case-insensitive header name.
	 *
	 * If the header does not appear in the message, this method MUST return an
	 * empty array.
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return string[] An array of string values as provided for the given
	 *    header. If the header does not appear in the message, this method MUST
	 *    return an empty array.
	 */
	public function getHeader(string $name)
	{
		return isset($this->headers[$name])
			? $this->headers[$name]
			: null;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves all message header values.
	 *
	 * The keys represent the header name as it will be sent over the wire, and
	 * each value is an array of strings associated with the header.
	 *
	 *     // Represent the headers as a string
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         echo $name . ": " . implode(", ", $values);
	 *     }
	 *
	 *     // Emit headers iteratively:
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         foreach ($values as $value) {
	 *             header(sprintf('%s: %s', $name, $value), false);
	 *         }
	 *     }
	 *
	 * While header names are not case-sensitive, getHeaders() will preserve the
	 * exact case in which headers were originally specified.
	 *
	 * @return mixed
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves the HTTP protocol version as a string.
	 *
	 * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
	 *
	 * @return string HTTP protocol version.
	 */
	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the message body.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function setBody(&$data)
	{
		if ($this->isComplete)
		{
			throw new \RuntimeException(\get_class($this).' instance already finalized');
		}

		$this->body = $data;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the body of the message.
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	//--------------------------------------------------------------------

	/**
	 * Marks this message as being complete. Once marked complete,
	 * no other changes can be made to this message.
	 *
	 * @return $this
	 */
	public function complete()
	{
		$this->isComplete = true;

		return $this;
	}

	//--------------------------------------------------------------------

}
