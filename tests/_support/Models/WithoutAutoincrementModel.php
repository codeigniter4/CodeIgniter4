<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class WithoutAutoincrementModel extends Model
{
	protected $table = 'without_autoincrement';

	protected $allowedFields = [
		'key',
		'value',
	];

	protected $returnType = 'object';

	protected $useSoftDeletes = false;
	
	protected $hasAutoincrement = false;
}
