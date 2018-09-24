<?php namespace CodeIgniter\HTTP;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 4.0.0
 * @filesource
 */
use CodeIgniter\HTTP\Exceptions\HTTPException;
use BadMethodCallException;
use CodeIgniter\Files\File;
use Config\Mimes;

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

	public function __construct(string $filename, bool $setMime)
	{
		$this->filename = $filename;
		$this->setMime = $setMime;
	}

	/**
	 * set download for binary string.
	 *
	 * @param string $binary
	 */
	public function setBinary(string $binary)
	{
		if ($this->file !== null) {
			throw new BadMethodCallException('When setting filepath can not set binary.');
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
		if ($this->binary !== null) {
			throw new BadMethodCallException('When setting binary can not set filepath.');
		}

		$this->file = new File($filepath, true);
	}

	/**
	 * get content length.
	 *
	 * @return int
	 */
	public function getContentLength() : int
	{
		if (is_string($this->binary)) {
			return strlen($this->binary);
		} elseif ($this->file instanceof File) {
			return $this->file->getSize();
		}

		return 0;
	}

	/**
	 * get mimetype
	 *
	 * @return string
	 */
	private function setContentTypeByMimeType()
	{
		$mime = null;
		$charset = '';

		if ($this->setMime === true)
		{
			if (($last_dot_position = strrpos($this->filename, '.')) !== false) {
				$mime = Mimes::guessTypeFromExtension(substr($this->filename, $last_dot_position + 1));
				$charset = $this->charset;
			}
		}

		if (!is_string($mime)) {
			// Set the default MIME type to send
			$mime = 'application/octet-stream';
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
		$filename = $this->filename;
		$x = explode('.', $this->filename);

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
			$x[count($x)-1] = strtoupper($extension);
			$filename       = implode('.', $x);
		}

		return $filename;
	}

	/**
	 * get Content-Disponsition Header string.
	 *
	 * @return string
	 */
	private function getContentDisponsition() : string
	{
		return sprintf('attachment; filename="%s"', $this->getDownloadFileName());
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
	 * Return an instance with the specified status code and, optionally, reason phrase.
	 *
	 * If no reason phrase is specified, will default recommended reason phrase for
	 * the response's status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @param int    $code         The 3-digit integer result code to set.
	 * @param string $reason       The reason phrase to use with the
	 *                             provided status code; if none is provided, will
	 *                             default to the IANA name.
	 *
	 * @return self
	 * @throws \InvalidArgumentException For invalid status code arguments.
	 */
	public function setStatusCode(int $code, string $reason = '')
	{
		if ($code !== 200) {
			throw HTTPException::forInvalidStatusCode($code);
		}

		if (!empty($reason) && $this->reason !== $reason) {
			$this->reason = $reason;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the response response phrase associated with the status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @return string
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
	 * Sets the date header
	 *
	 * @param \DateTime $date
	 *
	 * @return Response
	 */
	public function setDate(\DateTime $date)
	{
		$date->setTimezone(new \DateTimeZone('UTC'));

		$this->setHeader('Date', $date->format('D, d M Y H:i:s').' GMT');

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the Content Type header for this response with the mime type
	 * and, optionally, the charset.
	 *
	 * @param string $mime
	 * @param string $charset
	 *
	 * @return Response
	 */
	public function setContentType(string $mime, string $charset = 'UTF-8')
	{
		// add charset attribute if not already there and provided as parm
		if ((strpos($mime, 'charset=') < 1) && ! empty($charset))
		{
			$mime .= '; charset='.$charset;
		}

		$this->removeHeader('Content-Type'); // replace existing content type
		$this->setHeader('Content-Type', $mime);
		$this->charset = $charset;

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Cache Control Methods
	//
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	//--------------------------------------------------------------------

	/**
	 * Sets the appropriate headers to ensure this response
	 * is not cached by the browsers.
	 */
	public function noCache(): self
	{
		$this->removeHeader('Cache-control');

		$this->setHeader('Cache-control', ['private', 'no-transform', 'no-store', 'must-revalidate']);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * A shortcut method that allows the developer to set all of the
	 * cache-control headers in one method call.
	 *
	 * The options array is used to provide the cache-control directives
	 * for the header. It might look something like:
	 *
	 *      $options = [
	 *          'max-age'  => 300,
	 *          's-maxage' => 900
	 *          'etag'     => 'abcde',
	 *      ];
	 *
	 * Typical options are:
	 *  - etag
	 *  - last-modified
	 *  - max-age
	 *  - s-maxage
	 *  - private
	 *  - public
	 *  - must-revalidate
	 *  - proxy-revalidate
	 *  - no-transform
	 *
	 * @param array $options
	 *
	 * @return Response
	 */
	public function setCache(array $options = [])
	{
		// @todo: Should I make exceptions?
		throw new BadMethodCallException('It does not supported caching for downloading.');
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param string|\DateTime $date
	 */
	public function setLastModified($date)
	{
		if ($date instanceof \DateTime)
		{
			$date->setTimezone(new \DateTimeZone('UTC'));
			$this->setHeader('Last-Modified', $date->format('D, d M Y H:i:s').' GMT');
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

	public function pretend(bool $pretend = true)
	{
		$this->pretend = $pretend;

		return $this;
	}

	/**
	 * Sends the output to the browser.
	 *
	 * @return Response
	 */
	public function send()
	{
		return $this;
	}

	/**
	 * set header for file download.
	 */
	public function buildHeaders()
	{
		if (!$this->hasHeader('Content-Type')) {
			$this->setContentTypeByMimeType();
		}

		$this->setHeader('Content-Disposition', $this->getContentDisponsition());
		$this->setHeader('Expires-Disposition', '0');
		$this->setHeader('Content-Transfer-Encoding', 'binary');
		$this->setHeader('Content-Length', (string)$this->getContentLength());
		$this->noCache();
	}

	/**
	 * output donload file text.
	 *
	 * @return DownloadResponse
	 */
	public function sendBody()
	{
		if ($this->binary !== null) {
			return $this->sendBodyByBinary();
		} elseif ($this->file !== null) {
			return $this->sendBodyByFilePath();
		}

		throw new RuntimeException();
	}

	/**
	 * output download text by file.
	 *
	 * @return string
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
	 * @return string
	 */
	private function sendBodyByBinary()
	{
		echo $this->binary;

		return $this;
	}
}
