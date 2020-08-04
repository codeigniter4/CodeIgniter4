<?php namespace App\Controllers;

use CodeIgniter\Services;

class Home extends BaseController
{
	public function index()
	{
		return view('welcome_message');
	}

	//--------------------------------------------------------------------

}
