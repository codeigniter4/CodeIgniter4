<?php namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
		$this->logger->emergency('This will not be written to a file in the WRITEPATH/uploads folder');
		return view('welcome_message');
	}

	//--------------------------------------------------------------------

}
