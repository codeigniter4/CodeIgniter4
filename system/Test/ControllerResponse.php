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

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Testable response from a controller
 *
 * @deprecated Use TestResponse directly
 *
 * @codeCoverageIgnore
 */
class ControllerResponse extends TestResponse
{
	/**
	 * The message payload.
	 *
	 * @var string
	 *
	 * @deprecated Use $response->getBody() instead
	 */
	protected $body;

	/**
	 * DOM for the body.
	 *
	 * @var DOMParser
	 *
	 * @deprecated Use $domParser instead
	 */
	protected $dom;

	/**
	 * Maintains the deprecated $dom property.
	 */
	public function __construct()
	{
		parent::__construct(Services::response());

		$this->dom = &$this->domParser;
	}

	/**
	 * Sets the response.
	 *
	 * @param ResponseInterface $response
	 *
	 * @return $this
	 *
	 * @deprecated Will revert to parent::setResponse() in a future release (no $body updates)
	 */
	public function setResponse(ResponseInterface $response)
	{
		parent::setResponse($response);

		$this->body = $response->getBody() ?? '';

		return $this;
	}

	/**
	 * Sets the body and updates the DOM.
	 *
	 * @param string $body
	 *
	 * @return $this
	 *
	 * @deprecated Use response()->setBody() instead
	 */
	public function setBody(string $body)
	{
		$this->body = $body;

		if ($body !== '')
		{
			$this->domParser->withString($body);
		}

		return $this;
	}

	/**
	 * Retrieve the body.
	 *
	 * @return string
	 *
	 * @deprecated Use response()->getBody() instead
	 */
	public function getBody()
	{
		return $this->body;
	}
}
