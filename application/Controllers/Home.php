<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Home extends Controller
{
	public function index()
	{helper('inflector');
		return view('welcome_message');
	}

	//--------------------------------------------------------------------

}
