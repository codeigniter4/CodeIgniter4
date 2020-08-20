<?php
namespace Tests\Support\Filters;


class Customfilter implements \CodeIgniter\Filters\FilterInterface
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