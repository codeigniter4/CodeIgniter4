<?php namespace CodeIgniter\HTTP;

use App\Config\AppConfig;
use App\Config\ContentSecurityPolicyConfig;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * @package CodeIgniter\HTTP
 */
class Response extends Message implements ResponseInterface
{
	protected static $statusCodes = [
		// 1xx: Informational
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',    // http://www.iana.org/go/rfc2518

		// 2xx: Success
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information', // 1.1
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',                  // http://www.iana.org/go/rfc4918
		208 => 'Already Reported',              // http://www.iana.org/go/rfc5842
		226 => 'IM Used',                       // 1.1; http://www.ietf.org/rfc/rfc3229.txt

		// 3xx: Redirection
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',              // Formerly 'Moved Temporarily'
		303 => 'See Other',          // 1.1
		304 => 'Not Modified',
		305 => 'Use Proxy',          // 1.1
		306 => 'Switch Proxy',       // No longer used
		307 => 'Temporary Redirect', // 1.1
		308 => 'Permanent Redirect', // 1.1; Experimental; http://www.ietf.org/rfc/rfc7238.txt

		// 4xx: Client error
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => "I'm a teapot",                    // April's Fools joke; http://www.ietf.org/rfc/rfc2324.txt
		// 419 (Authentication Timeout) is a non-standard status code with unknown origin
		421 => 'Misdirected Request',             // http://www.iana.org/go/rfc7540 Section 9.1.2
		422 => 'Unprocessable Entity',            // http://www.iana.org/go/rfc4918
		423 => 'Locked',                          // http://www.iana.org/go/rfc4918
		424 => 'Failed Dependency',               // http://www.iana.org/go/rfc4918
		426 => 'Upgrade Required',
		428 => 'Precondition Required',           // 1.1; http://www.ietf.org/rfc/rfc6585.txt
		429 => 'Too Many Requests',               // 1.1; http://www.ietf.org/rfc/rfc6585.txt
		431 => 'Request Header Fields Too Large', // 1.1; http://www.ietf.org/rfc/rfc6585.txt

		// 5xx: Server error
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',        // 1.1; http://www.ietf.org/rfc/rfc2295.txt
		507 => 'Insufficient Storage',           // http://www.iana.org/go/rfc4918
		508 => 'Loop Detected',                  // http://www.iana.org/go/rfc5842
		510 => 'Not Extended',                   // http://www.ietf.org/rfc/rfc2774.txt
		511 => 'Network Authentication Required' // http://www.ietf.org/rfc/rfc6585.txt
	];

	/**
	 * The current reason phrase for this response.
	 * If null, will use the default provided for the status code.
	 *
	 * @var string
	 */
	protected $reason;

	/**
	 * The current status code for this response.
	 *
	 * @var int
	 */
	protected $statusCode;

	/**
	 * Whether Content Security Policy is being enforced.
	 * @var bool
	 */
	protected $CSPEnabled = false;

	/**
	 * @var \CodeIgniter\HTTP\ContentSecurityPolicy
	 */
	protected $CSP;

	//--------------------------------------------------------------------

	public function __construct(AppConfig $config)
	{
	    // Default to a non-caching page.
		// Also ensures that a Cache-control header exists.
		$this->noCache();

		// Are we enforcing a Content Security Policy?
		if ($config->CSPEnabled === true)
		{
			$this->CSP = new ContentSecurityPolicy(new ContentSecurityPolicyConfig());
			$this->CSPEnabled = true;
		}
	}

	//--------------------------------------------------------------------



	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit integer result code of the server's attempt
	 * to understand and satisfy the request.
	 *
	 * @return int Status code.
	 */
	public function getStatusCode(): int
	{
		if (empty($this->statusCode))
		{
			throw new \BadMethodCallException('HTTP Response is missing a status code');
		}

		return $this->statusCode;
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
	 * @param string $reasonPhrase The reason phrase to use with the
	 *                             provided status code; if none is provided, will
	 *                             default to the IANA name.
	 *
	 * @return self
	 * @throws \InvalidArgumentException For invalid status code arguments.
	 */
	public function setStatusCode(int $code, string $reason = ''): self
	{
		// Valid range?
		if ($code < 100 || $code > 599)
		{
			throw new \InvalidArgumentException($code.' is not a valid HTTP return status code');
		}

		// Unknown and no message?
		if (! array_key_exists($code, static::$statusCodes) && empty($reason))
		{
			throw new \InvalidArgumentException('Unknown HTTP status code provided with no message');
		}

		$this->statusCode = $code;

		if (! empty($reason))
		{
			$this->reason = $reason;
		}
		else
		{
			$this->reason = static::$statusCodes[$code];
		}

		return $this;
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
		if (empty($this->reason))
		{
			return ! empty($this->statusCode)
				? static::$statusCodes[$this->statusCode]
				: '';
		}

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
	public function setDate(\DateTime $date): self
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
	public function setContentType(string $mime, string $charset='UTF-8'): self
	{
	    if (! empty($charset))
	    {
		    $mime .= '; charset='. $charset;
	    }

		$this->setHeader('Content-Type', $mime);

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

		$this->setHeader('Cache-control', ['no-store', 'max-age=0', 'no-cache']);

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
	 * @return $this
	 */
	public function setCache(array $options=[]): self
	{
		if (empty($options))
		{
			return $this;
		}

		$this->removeHeader('Cache-Control');
		$this->removeHeader('ETag');

		// ETag
		if (isset($options['etag']))
		{
			$this->setHeader('ETag', $options['etag']);
			unset($options['etag']);
		}

		// Last Modified
		if (isset($options['last-modified']))
		{
			$this->setLastModified($options['last-modified']);

			unset($options['last-modified']);
		}

		$this->setHeader('Cache-control', $options);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param $date
	 */
	public function setLastModified($date): self
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

	/**
	 * Sends the output to the browser.
	 *
	 * @return Response
	 */
	public function send(): self
	{
		// If we're enforcing a Content Security Policy,
		// we need to give it a chance to build out it's headers.
		if ($this->CSPEnabled === true)
		{
			$this->CSP->finalize($this);
		}

	    $this->sendHeaders();
		$this->sendBody();

		return $this;
	}
	
	//--------------------------------------------------------------------

	/**
	 * Sends the headers of this HTTP request to the browser.
	 *
	 * @return Response
	 */
	public function sendHeaders(): self
	{
	    // Have the headers already been sent?
		if (headers_sent())
		{
			return $this;
		}

		// Per spec, MUST be sent with each request, if possible.
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
		if (isset($this->headers['Date']))
		{
			$this->setDate(\DateTime::createFromFormat('U', time()));
		}

		// HTTP Status
		header(sprintf('HTTP/%s %s %s', $this->protocolVersion, $this->statusCode, $this->reason), true, $this->statusCode);

		// Send all of our headers
		foreach ($this->getHeaders() as $name => $values)
		{
			header($name.': '.$this->getHeaderLine($name), false, $this->statusCode);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sends the Body of the message to the browser.
	 *
	 * @return $this
	 */
	public function sendBody()
	{
	    echo $this->body;

		return $this;
	}

	//--------------------------------------------------------------------



}
