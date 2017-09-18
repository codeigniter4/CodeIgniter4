<?php namespace Tests\Support\Helpers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerResponse {

	/**
	 * @var \CodeIgniter\HTTP\IncomingRequest
	 */
	protected $request;

	/**
	 * @var \CodeIgniter\HTTP\Response
	 */
	protected $response;

	/**
	 * @var string
	 */
	protected $body;

	//--------------------------------------------------------------------
	// Getters / Setters
	//--------------------------------------------------------------------

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody(string $body)
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}


	/**
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 *
	 * @return $this
	 */
	public function setRequest(RequestInterface $request)
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * @param \CodeIgniter\HTTP\ResponseInterface $response
	 *
	 * @return $this
	 */
	public function setResponse(ResponseInterface $response)
	{
		$this->response = $response;

		return $this;
	}

	/**
	 * @return \CodeIgniter\HTTP\IncomingRequest
	 */
	public function request()
	{
		return $this->request;
	}

	/**
	 * @return \CodeIgniter\HTTP\Response
	 */
	public function response()
	{
		return $this->response;
	}

	//--------------------------------------------------------------------
	// Simple Response Checks
	//--------------------------------------------------------------------

	/**
	 * Boils down the possible responses into a bolean valid/not-valid
	 * response type.
	 *
	 * @return bool
	 */
	public function isOK(): bool
	{
		// Only 200 and 300 range status codes
		// are considered valid.
		if ($this->response->getStatusCode() >= 400 || $this->response->getStatusCode() < 200)
		{
			return false;
		}

		// Empty bodies are not considered valid.
		if (empty($this->response->getBody()))
		{
			return false;
		}

		return true;
	}

}
