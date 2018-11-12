<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class EntityModel extends Model
{
	protected $table = 'job';

	protected $returnType = '\Tests\Support\Models\SimpleEntity';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'datetime';

	protected $allowedFields = [
		'name',
		'description',
		'created_at',
	];
}
