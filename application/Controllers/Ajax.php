<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Ajax extends Controller
{
	public function index()
	{
		return view('ajax');
	}

	public function post()
	{
		return $this->response->setHeader('Content-Type', 'application/json')
							  ->setBody(json_encode($this->request->getVar()));
	}
}
