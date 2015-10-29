<?php namespace CodeIgniter\HTTP;

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
class Response extends Message
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
	protected $statusCode = 200;

	//--------------------------------------------------------------------

	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit integer result code of the server's attempt
	 * to understand and satisfy the request.
	 *
	 * @return int Status code.
	 */
	public function statusCode(): int
	{
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
		if (! array_key_exists($code, static::$statusCodes))
		{
			throw new \InvalidArgumentException($code.' is not a valid status code.');
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
	public function reason(): string
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


}
