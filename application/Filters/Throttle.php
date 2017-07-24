<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Throttle implements FilterInterface
{
	/**
	 * This is a demo implementation of using the Throttler class
	 * to implement rate limiting for your application.
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request)
	{
		$throttler = Services::throttler();

		// Restrict an IP address to no more
		// than 1 request per second across the
		// entire site.
		if ($throttler->check($request->getIPAddress(), 60, MINUTE) === false)
		{
			return Services::response()->setStatusCode(429);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * We don't have anything to do here.
	 *
	 * @param RequestInterface|\CodeIgniter\HTTP\IncomingRequest $request
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response)
	{
	}

	//--------------------------------------------------------------------
}
