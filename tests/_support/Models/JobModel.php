<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
	protected $table = 'job';

	protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'integer';

	protected $allowedFields = [
		'name',
		'description',
	];
}
