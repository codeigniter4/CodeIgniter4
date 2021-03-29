<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class SecondaryModel extends Model
{
	protected $table = 'secondary';

	protected $primaryKey = 'id';

	protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'int';

	protected $allowedFields = [
		'key',
		'value',
	];
}
