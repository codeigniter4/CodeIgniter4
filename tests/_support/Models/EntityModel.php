<?php namespace Tests\Support\Models;

use CodeIgniter\Model;

class EntityModel extends Model
{
	protected $table = 'job';

	protected $returnType = '\Tests\Support\Models\SimpleEntity';
//    protected $returnType = 'object';

	protected $useSoftDeletes = false;

	protected $dateFormat = 'integer';

    protected $allowedFields = [
        'name', 'description'
    ];
}
