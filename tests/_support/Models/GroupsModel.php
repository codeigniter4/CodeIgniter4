<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;

class GroupsModel extends Model
{
	protected $table = 'groups';

	protected $returnType = 'object';

	protected $useTimestamps = true;

	protected $dateFormat = 'datetime';

	protected $allowedFields = [
		'name',
		'description',
	];
}
