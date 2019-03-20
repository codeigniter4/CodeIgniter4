<?php namespace App\Controllers;

use CodeIgniter\Controller;

class BaseController extends Controller
{
	protected $helpers = [ ];

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// Autoload any models, libraries, etc, here.

	}
}
