<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table = 'user';

	protected $allowedFields = [
		'name',
		'email',
		'country',
		'deleted',
	];

	protected $returnType = 'object';

	protected $useSoftDeletes = true;

	protected $dateFormat = 'datetime';

	public $name = '';

	public $email = '';

	public $country = '';
}
