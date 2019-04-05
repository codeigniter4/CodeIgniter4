<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Home extends BaseController
{
	public function index()
	{
		$model = new UserModel();
		dd($model->findAll());

		return view('welcome_message');
	}

	//--------------------------------------------------------------------

}
