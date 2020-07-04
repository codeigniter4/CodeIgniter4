<?php
namespace CodeIgniter\Filters\fixtures;

use CodeIgniter\Filters\FilterInterfaceWithArguments;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ArgumentsFilter implements FilterInterfaceWithArguments
{

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = [])
	{
		if (is_null($arguments) || count($arguments) === 0)
		{
			$response->setBody('You gave after() no arguments');
		}
		else
		{
			$response->setBody('You gave after() arguments ' . join(',', $arguments));
		}
		return $response;
	}

	public function before(RequestInterface $request, $arguments = [])
	{
		if (is_null($arguments) || count($arguments) === 0)
		{
			return 'You gave before() no arguments';
		}
		else
		{
			return 'You gave before() arguments ' . join(',', $arguments);
		}
	}

}
