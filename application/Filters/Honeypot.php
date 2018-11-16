<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;

class Honeypot implements FilterInterface
{

	/**
	 * Checks if Honeypot field is empty; if not
	 * then the requester is a bot
	 *
	 * @param CodeIgniter\HTTP\RequestInterface $request
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request)
	{
		$honeypot = Services::honeypot(new \Config\Honeypot());
		if ($honeypot->hasContent($request))
		{
			throw HoneypotException::isBot();
		}
	}

	/**
	 * Attach a honypot to the current response.
	 *
	 * @param  CodeIgniter\HTTP\RequestInterface  $request
	 * @param  CodeIgniter\HTTP\ResponseInterface $response
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response)
	{
		$honeypot = Services::honeypot(new \Config\Honeypot());
		$honeypot->attachHoneypot($response);
	}

}
