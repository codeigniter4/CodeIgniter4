<?php namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GoogleCurious implements FilterInterface
{

	public function before(RequestInterface $request, $arguments = null)
	{
				return 'This is curious';
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}

}
