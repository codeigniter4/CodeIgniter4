<?php namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GoogleYou implements FilterInterface
{

	public function before(RequestInterface $request)
	{
		$response      = Services::response();
		$response->csp = 'http://google.com';
		return $response;
	}

	public function after(RequestInterface $request, ResponseInterface $response)
	{
	}

}
