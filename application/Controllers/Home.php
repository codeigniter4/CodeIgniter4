<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Home extends Controller
{
	public function index()
	{
		dd($this);
		return view('welcome_message');
	}

	//--------------------------------------------------------------------

}
