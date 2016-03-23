<?php

class Home extends \CodeIgniter\Controller
{

	public function index()
	{
		$db = \Config\Database::connect('default');

		$results = $db->query("SELECT * FROM tracks");

		die(var_dump($results));

		echo load_view('welcome_message');
	}

	//--------------------------------------------------------------------

}
