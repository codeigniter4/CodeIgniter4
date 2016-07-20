<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use Config\Services;

class CSRF implements FilterInterface
{
	/**
	 * Do whatever processing this filter needs to do.
	 * By default it should not return anything during
	 * normal execution. However, when an abnormal state
	 * is found, it should return an instance of
	 * CodeIgniter\HTTP\Response. If it does, script
	 * execution will end and that Response will be
	 * sent back to the client, allowing for error pages,
	 * redirects, etc.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface $request
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request)
	{
		if ($request->isCLI())
		{
			return;
		}

		$security = Services::security();

		$security->CSRFVerify($request);
	}

	//--------------------------------------------------------------------

	/**
	 * We don't have anything to do here.
	 *
	 * @param \CodeIgniter\HTTP\RequestInterface  $request
	 * @param \CodeIgniter\HTTP\ResponseInterface $response
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response)
	{
	}

	//--------------------------------------------------------------------
}
