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

class Message
{
	use HeaderTrait;

	/**
	 * Protocol version
	 * @var type
	 */
	protected $protocolVersion;

	/**
	 * List of valid protocol versions
	 * @var array
	 */
	protected $validProtocolVersions = ['1.0', '1.1', '2'];

	/**
	 * Message body
	 *
	 * @var type
	 */
	protected $body;

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Body
	//--------------------------------------------------------------------

	/**
	 * Returns the Message's body.
	 *
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the body of the current message.
	 *
	 * @param $data
	 *
	 * @return Message
	 */
	public function setBody(&$data): self
	{
		$this->body = $data;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Appends data to the body of the current message.
	 *
	 * @param $data
	 *
	 * @return \CodeIgniter\HTTP\Message
	 */
	public function appendBody($data): self
	{
	    $this->body .= (string)$data;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Populates the $headers array with any headers the getServer knows about.
	 */
	public function populateHeaders()
	{
		$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : getenv('CONTENT_TYPE');
		if (! empty($contentType))
		{
			$this->setHeader('Content-Type', $contentType);
		}
		unset($contentType);

		foreach ($_SERVER as $key => $val)
		{
			if (sscanf($key, 'HTTP_%s', $header) === 1)
			{
				// take SOME_HEADER and turn it into Some-Header
				$header = str_replace('_', ' ', strtolower($header));
				$header = str_replace(' ', '-', ucwords($header));

				if (array_key_exists($key, $_SERVER))
				{
					$this->setHeader($header, $_SERVER[$key]);
				}
				else
				{
					$this->setHeader($header, '');
				}

				// Add us to the header map so we can find them case-insensitively
				$this->headerMap[strtolower($header)] = $header;
			}
		}
	}

	//--------------------------------------------------------------------


	/**
	 * Returns the HTTP Protocol Version.
	 *
	 * @return string
	 */
	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the HTTP protocol version.
	 *
	 * @param string $version
	 *
	 * @return Message
	 */
	public function setProtocolVersion(string $version): self
	{
		if (! is_numeric($version))
		{
			$version = substr($version, strpos($version, '/') + 1);
		}

	    if (! in_array($version, $this->validProtocolVersions))
	    {
		    throw new \InvalidArgumentException('Invalid HTTP Protocol Version. Must be one of: '. implode(', ', $this->validProtocolVersions));
	    }

		$this->protocolVersion = $version;

		return $this;
	}

	//--------------------------------------------------------------------

}
