<?php namespace Tests\Support\Models;

use CodeIgniter\Model;
use Faker\Generator;

class FabricatorModel extends Model
{
	protected $table = 'job';

	protected $returnType = 'object';

	protected $useSoftDeletes = true;

	protected $useTimestamps = true;

	protected $dateFormat = 'int';

	protected $allowedFields = [
		'name',
		'description',
	];

	// Return a faked entity
	public function fake(Generator &$faker)
	{
		return (object) [
							'name'        => $faker->ipv4,
							'description' => $faker->words(10),
						];
	}
}
