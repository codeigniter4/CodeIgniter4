<?php namespace CodeIgniter;

use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;

class Controller
{
	protected $request;

	protected $response;

	//--------------------------------------------------------------------

	public function __construct(Request $request, Response $response)
	{
	    $this->request = $request;

		$this->response = $response;
	}
	
	//--------------------------------------------------------------------
	
	
}