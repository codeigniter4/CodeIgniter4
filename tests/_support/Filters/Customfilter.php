<?php
namespace Tests\Support\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Services;

class Customfilter implements FilterInterface
{

	public function before(RequestInterface $request, $arguments = null)
	{
		$request->url = 'http://hellowworld.com';

		return $request;
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}

	//--------------------------------------------------------------------
}
