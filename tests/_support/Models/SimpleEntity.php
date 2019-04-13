<?php namespace Tests\Support\Models;

use CodeIgniter\Entity;

/**
 * Class SimpleEntity
 *
 * Simple Entity-type class for testing creating and saving entities
 * in the model so we can support Entity/Repository type patterns.
 *
 * @package Tests\Support\Models
 */
class SimpleEntity extends Entity
{
	protected $id;
	protected $name;
	protected $description;
	protected $deleted;
	protected $created_at;
	protected $updated_at;

	protected $_options = [
		'datamap' => [],
		'dates'   => [
			'created_at',
			'updated_at',
			'deleted_at',
		],
		'casts'   => [],
	];
}
