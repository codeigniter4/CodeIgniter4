<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;

class WithoutAutoincrementModel extends Model
{
	protected $table = 'without_auto_increment';

	protected $primaryKey = 'key';

	protected $allowedFields = [
		'key',
		'value',
	];

	protected $useAutoIncrement = false;
}
