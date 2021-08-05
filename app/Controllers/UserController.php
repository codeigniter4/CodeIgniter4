<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
	public function index()
	{
		$user        = new UserModel();
		$operaciones = $user->find(1);

		//var_dump($operaciones);
		//unset($operaciones['id']);
		$user->save($operaciones);
		var_dump($user->db->getLastQuery());
	}
}
