<?php namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel Model
 */
class UserModel extends Model
{
	protected $table      = 'users';
	protected $primaryKey = 'id';

	protected $allowedFields = [
		'name',
		'email',
	];

	protected $returnType     = 'array';
	protected $useSoftDeletes = false;

	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $dateFormat    = 'datetime';

	protected $validationRules = [
		'id'    => 'integer|max_length[11]',
		'name'  => 'alpha_numeric_spaces|max_length[255]',
		'email' => 'alpha_numeric_spaces|max_length[255]',

	];
	protected $validationMessages = [];
	protected $skipValidation     = false;
}
