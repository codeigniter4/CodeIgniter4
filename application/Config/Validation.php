<?php namespace Config;

class Validation
{
	//--------------------------------------------------------------------
	// Setup
	//--------------------------------------------------------------------

	/**
	 * Stores the classes that contain the
	 * rules that are available.
	 *
	 * @var array
	 */
	public $ruleSets = [
		\CodeIgniter\Validation\Rules\Required::class,
		\CodeIgniter\Validation\Rules\AlphaFormat::class,
		\CodeIgniter\Validation\Rules\DateTimeFormat::class,
		\CodeIgniter\Validation\Rules\NumberFormat::class,
		\CodeIgniter\Validation\Rules\Format::class,
		\CodeIgniter\Validation\Rules\Comparison::class,
		\CodeIgniter\Validation\Rules\DatabaseDependency::class,
		\CodeIgniter\Validation\Rules\Length::class,
		\CodeIgniter\Validation\Rules\File::class,
		\CodeIgniter\Validation\Rules\CreditCard::class,
	];

	/**
	 * Specifies the views that are used to display the
	 * errors.
	 *
	 * @var array
	 */
	public $templates = [
		'list'   => 'CodeIgniter\Validation\Views\list',
		'single' => 'CodeIgniter\Validation\Views\single'
	];

	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------
}
