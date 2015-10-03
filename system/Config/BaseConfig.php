<?php namespace CodeIgniter\Config;

/**
 * Class BaseConfig
 *
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values
 * from the environment.
 *
 * These can be set within the .env file.
 *
 * @package App\Config
 */
class BaseConfig
{
	/**
	 * Will attempt to get environment variables with names
	 * that match the properties of the child class.
	 */
	public function __construct()
	{
		$properties = array_keys(get_object_vars($this));

		foreach ($properties as $property)
		{
			if ($value = getenv($property))
			{
				$this->{$property} = $value;
			}
		}
	}

	//--------------------------------------------------------------------

}
