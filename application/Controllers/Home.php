<?php namespace App\Controllers;

class Home extends \CodeIgniter\Controller
{
	public function index()
	{
		echo view('welcome_message');
	}

	//--------------------------------------------------------------------

}
