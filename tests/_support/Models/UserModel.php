<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table = 'user';

	protected $returnType = 'object';

	protected $useSoftDeletes = true;

	protected $dateFormat = 'integer';
}
