<?php

class Home extends \CodeIgniter\Controller
{

	public function index()
	{
		echo load_view('welcome_message');
	}

	//--------------------------------------------------------------------

}
