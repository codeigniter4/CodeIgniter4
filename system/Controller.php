<?php namespace CodeIgniter;

use CodeIgniter\HTTPLite\Request;
use CodeIgniter\HTTPLite\Response;

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