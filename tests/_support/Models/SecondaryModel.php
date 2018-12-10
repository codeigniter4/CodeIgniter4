<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class SecondaryModel extends Model
{
	protected $table = 'secondary';

	protected $primaryKey = null;

	protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'integer';

	protected $allowedFields = [
		'key',
		'value',
	];
}
