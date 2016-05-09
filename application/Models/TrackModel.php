<?php namespace App\Models;

use CodeIgniter\Model;

class TrackModel extends Model
{
	protected $table = 'users';

	protected $allowedFields = ['username'];
}

