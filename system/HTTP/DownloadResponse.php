<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
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
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Exceptions\DownloadException;
use CodeIgniter\Files\File;
use Config\Mimes;

/**
 * HTTP response when a download is requested.
 */
class DownloadResponse extends Message implements ResponseInterface
{
	/**
	 * Download file name
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Download for file
	 *
	 * @var File?
	 */
	private $file;

	/**
	 * mime set flag
	 *
	 * @var boolean
	 */
	private $setMime;

	/**
	 * Download for binary
	 *
	 * @var string
	 */
	private $binary;

	/**
	 * Download reason
	 *
	 * @var string
	 */
	private $reason = 'OK';

	/**
	 * Download charset
	 *
	 * @var string
	 */
	private $charset = 'UTF-8';

	/**
	 * pretend
	 *
	 * @var boolean
	 */
	private $pretend = false;

	/**
	 * Constructor.
	 *
	 * @param string  $filename
	 * @param boolean $setMime
	 */
	public function __construct(string $filename, bool $setMime)
	{
		$this->filename = $filename;
		$this->setMime  = $setMime;
	}

	/**
	 * set download for binary string.
	 *
	 * @param string $binary
	 */
	public function setBinary(string $binary)
	{
		if ($this->file !== null)
		{
			throw DownloadException::forCannotSetBinary();
		}

		$this->binary = $binary;
	}

	/**
	 * set download for file.
	 *
	 * @param string $filepath
	 */
	public function setFilePath(string $filepath)
	{
		if ($this->binary !== null)
		{
			throw DownloadException::forCannotSetFilePath($filepath);
		}

		$this->file = new File($filepath, true);
	}

	/**
	 * get content length.
	 *
	 * @return integer
	 */
	public function getContentLength() : int
	{
		if (is_string($this->binary))
		{
			return strlen($this->binary);
		}
		elseif ($this->file instanceof File)
		{
			return $this->file->getSize();
		}

		return 0;
	}

	/**
	 * Set content type by guessing mime type from file extension
	 */
	private function setContentTypeByMimeType()
	{
		$mime    = null;
		$charset = '';

		if ($this->setMime === true)
		{
			if (($last_dot_position = strrpos($this->filename, '.')) !== false)
			{
				$mime    = Mimes::guessTypeFromExtension(substr($this->filename, $last_dot_position + 1));
				$charset = $this->charset;
			}
		}

		if (! is_string($mime))
		{
			// Set the default MIME type to send
			$mime    = 'application/octet-stream';
			$charset = '';
		}

		$this->setContentType($mime, $charset);
	}

	/**
	 * get download filename.
	 *
	 * @return string
	 */
	private function getDownloadFileName(): string
	{
		$filename  = $this->filename;
		$x         = explode('.', $this->filename);
		$extension = end($x);

		/* It was reported that browsers on Android 2.1 (and possibly older as well)
		 * need to have the filename extension upper-cased in order to be able to
		 * download it.
		 *
		 * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
		 */
		// @todo: depend super global
		if (count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT'])
				&& preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT']))
		{
			$x[count($x) - 1] = strtoupper($extension);
			$filename         = implode('.', $x);
		}

		return $filename;
	}

	/**
	 * get Content-Disposition Header string.
	 *
	 * @return string
	 */
	private function getContentDisposition() : string
	{
		$download_filename = $this->getDownloadFileName();

		$utf8_filename = $download_filename;

		if (strtoupper($this->charset) !== 'UTF-8')
		{
			$utf8_filename = mb_convert_encoding($download_filename, 'UTF-8', $this->charset);
		}

		$result = sprintf('attachment; filename="%s"', $download_filename);

		if (isset($utf8_filename))
		{
			$result .= '; filename*=UTF-8\'\'' . rawurlencode($utf8_filename);
		}

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStatusCode(): int
	{
		return 200;
	}

	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 *
	 * @throws DownloadException
	 */
	public function setStatusCode(int $code, string $reason = '')
	{
		throw DownloadException::forCannotSetStatusCode($code, $reason);
	}

	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 */
	public function getReason(): string
	{
		return $this->reason;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Convenience Methods
	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 */
	public function setDate(\DateTime $date)
	{
		$date->setTimezone(new \DateTimeZone('UTC'));

		$this->setHeader('Date', $date->format('D, d M Y H:i:s') . ' GMT');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 */
	public function setContentType(string $mime, string $charset = 'UTF-8')
	{
		// add charset attribute if not already there and provided as parm
		if ((strpos($mime, 'charset=') < 1) && ! empty($charset))
		{
			$mime .= '; charset=' . $charset;
		}

		$this->removeHeader('Content-Type'); // replace existing content type
		$this->setHeader('Content-Type', $mime);
		if (! empty($charset))
		{
			$this->charset = $charset;
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function noCache(): self
	{
		$this->removeHeader('Cache-control');

		$this->setHeader('Cache-control', ['private', 'no-transform', 'no-store', 'must-revalidate']);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 *
	 * @throws DownloadException
	 */
	public function setCache(array $options = [])
	{
		throw DownloadException::forCannotSetCache();
	}

	//--------------------------------------------------------------------

	/**
	 * {@inheritDoc}
	 */
	public function setLastModified($date)
	{
		if ($date instanceof \DateTime)
		{
			$date->setTimezone(new \DateTimeZone('UTC'));
			$this->setHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
		}
		elseif (is_string($date))
		{
			$this->setHeader('Last-Modified', $date);
		}

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * For unit testing, don't actually send headers.
	 *
	 * @param  boolean $pretend
	 * @return $this
	 */
	public function pretend(bool $pretend = true)
	{
		$this->pretend = $pretend;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function send()
	{
		$this->buildHeaders();
		$this->sendHeaders();
		$this->sendBody();

		return $this;
	}

	/**
	 * set header for file download.
	 */
	public function buildHeaders()
	{
		if (! $this->hasHeader('Content-Type'))
		{
			$this->setContentTypeByMimeType();
		}

		$this->setHeader('Content-Disposition', $this->getContentDisposition());
		$this->setHeader('Expires-Disposition', '0');
		$this->setHeader('Content-Transfer-Encoding', 'binary');
		$this->setHeader('Content-Length', (string)$this->getContentLength());
		$this->noCache();
	}

	/**
	 * Sends the headers of this HTTP request to the browser.
	 *
	 * @return DownloadResponse
	 */
	public function sendHeaders()
	{
		// Have the headers already been sent?
		if ($this->pretend || headers_sent())
		{
			return $this;
		}

		// Per spec, MUST be sent with each request, if possible.
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
		if (! isset($this->headers['Date']))
		{
			$this->setDate(\DateTime::createFromFormat('U', time()));
		}

		// HTTP Status
		header(sprintf('HTTP/%s %s %s', $this->protocolVersion, $this->getStatusCode(), $this->getReason()), true,
				$this->getStatusCode());

		// Send all of our headers
		foreach ($this->getHeaders() as $name => $values)
		{
			header($name . ': ' . $this->getHeaderLine($name), false, $this->getStatusCode());
		}

		return $this;
	}

	/**
	 * output download file text.
	 *
	 * @throws DownloadException
	 *
	 * @return DownloadResponse
	 */
	public function sendBody()
	{
		if ($this->binary !== null)
		{
			return $this->sendBodyByBinary();
		}
		elseif ($this->file !== null)
		{
			return $this->sendBodyByFilePath();
		}

		throw DownloadException::forNotFoundDownloadSource();
	}

	/**
	 * output download text by file.
	 *
	 * @return DownloadResponse
	 */
	private function sendBodyByFilePath()
	{
		$spl_file_object = $this->file->openFile('rb');

		// Flush 1MB chunks of data
		while (! $spl_file_object->eof() && ($data = $spl_file_object->fread(1048576)) !== false)
		{
			echo $data;
		}

		return $this;
	}

	/**
	 * output download text by binary
	 *
	 * @return DownloadResponse
	 */
	private function sendBodyByBinary()
	{
		echo $this->binary;

		return $this;
	}
}
