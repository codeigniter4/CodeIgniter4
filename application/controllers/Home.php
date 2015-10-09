<?php

class Home {

	public function index()
	{
		throw new RuntimeException('Do not know whats going on.');
	    echo view('welcome_message');
	}

	//--------------------------------------------------------------------

}