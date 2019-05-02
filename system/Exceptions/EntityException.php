<?php namespace CodeIgniter\Exceptions;

/**
 * Entity Exceptions.
 */

class EntityException extends AlertError
{

	/**
	 * Error code
	 *
	 * @var integer
	 */
	protected $code = 3;

	public static function forTryingToAccessNonExistentProperty(string $property, string $on)
	{
		throw new static(lang('Entity.tryingToAccessNonExistentProperty', [$property, $on]));
	}

}
