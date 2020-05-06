<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Test;

use CodeIgniter\Model;
use Faker\Factory;
use Faker\Generator;

/**
 * Fabricator
 *
 * Bridge class for using Faker to create example data based on
 * model specifications.
 */
class Fabricator
{
	/**
	 * Locale-specific Faker instance
	 *
	 * @var \Faker\Generator
	 */
	protected $faker;

	/**
	 * Model instance
	 *
	 * @var \CodeIgniter\Model
	 */
	protected $model;

	/**
	 * Map of properties and their formatter to use
	 *
	 * @var array
	 */
	protected $formatters;

	/**
	 * Default formatter to use when nothing is detected
	 *
	 * @var string
	 */
	public $defaultFormatter = 'word';

	//--------------------------------------------------------------------

	/**
	 * Store the model instance and initialize Faker to the locale.
	 *
	 * @param string|Model $model      Instance or classname of the model to use
	 * @param string|null  $locale     Locale for Faker provider
	 * @param array        $formatters Array of property => formatter
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($model, string $locale = null, array $formatters = null)
	{
		if (is_string($model))
		{
			$model = new $model();
		}

		// Verify the model
		if (! $model instanceof Model)
		{
			throw new \InvalidArgumentException(lang('Fabricator.invalidModel'));
		}

		// If no locale was specified then use the App default
		if (is_null($locale))
		{
			$locale = config('App')->defaultLocale;
		}

		// Will throw \InvalidArgumentException for unmatched locales
		$this->faker = Factory::create($locale);

		// Set the formatters
		$this->setFormatters($formatters);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the model instance
	 *
	 * @return CodeIgniter\Model
	 */
	public function getModel(): Model
	{
		return $this->model;
	}

	/**
	 * Returns the Faker generator
	 *
	 * @return Faker\Generator
	 */
	public function getFaker(): Faker
	{
		return $this->faker;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current formatters
	 *
	 * @return array|null
	 */
	public function getFormatters(): ?array
	{
		return $this->formatters;
	}

	/**
	 * Set the formatters to use. Will attempt to autodetect if none are available.
	 *
	 * @return $this
	 */
	public function setFormatters(array $formatters = null): self
	{
		if (! is_null($formatters))
		{
			$this->formatters = $formatters;
		}
		elseif (method_exists($this->model, 'fake'))
		{
			$this->formatters = null;
		}
		else
		{
			$formatters = $this->detectFormatters();
		}

		return $this->faker;
	}

	/**
	 * Try to identify the appropriate Faker formatter for each field.
	 *
	 * @return $this
	 */
	protected function detectFormatters(): self
	{
		$this->formatters = [];

		foreach ($this->model->allowedFields as $field)
		{
			$this->formatters[$field] = $this->guessFormatter($field);
		}

		return $this;
	}

	/**
	 * Guess at the correct formatter to match a field name.
	 *
	 * @param $field  Name of the field
	 *
	 * @return string  Name of the formatter
	 */
	protected function guessFormatter($field): self
	{
		// First check for a Faker formatter of the same name - covers things like "email"
		try
		{
			$this->faker->getFormatter($field);
			return $field;
		}
		catch (\InvalidArgumentException $e)
		{
			// No match, keep going
		}

		// Check some common partials
		foreach (['email', 'name', 'title', 'text', 'date', 'url'] as $term)
		{
			if (stripos($field, $term) !== false)
			{
				return $field;
			}
		}

		if (stripos($field, 'phone') !== false)
		{
			return 'phoneNumber';
		}

		// Nothing left, use the default
		return $this->defaultFormatter;
	}

	//--------------------------------------------------------------------

	/**
	 * Generate new entities with faked data
	 *
	 * @param integer|null $count    Optional number to create a collection
	 * @param array        $override Array of data to add/override
	 *
	 * @return array|object  An array or object (based on returnType), or an array of returnTypes
	 */
	public function make(int $count = null, array $override = [])
	{
		// If a singleton was requested then go straight to it
		if (is_null($count))
		{
			return $this->model->returnType === 'array' ?
				$this->makeArray($override) :
				$this->makeObject($override);
		}

		$return = [];

		for ($i = 0; $i < $count; $i++)
		{
			$return[] = $this->model->returnType === 'array' ?
				$this->makeArray($override) :
				$this->makeObject($override);
		}

		return $return;
	}

	/**
	 * Generate an array of faked data
	 *
	 * @param array $override Array of data to add/override
	 *
	 * @return array  An array of faked data
	 *
	 * @throws \RuntimeException
	 */
	protected function makeArray(array $override = [])
	{
		if (! is_null($this->formatters))
		{
			$result = [];

			foreach ($this->formatters as $field => $formatter)
			{
				$result[$field] = $this->faker->{$formatter};
			}
		}

		// If no formatters were defined then look for a model fake() method
		elseif (method_exists($this->model, 'fake'))
		{
			$result = $this->model->fake();

			// This should cover entities
			if (method_exists($result, 'toArray'))
			{
				$result = $result->toArray();
			}
			// Try to cast it
			else
			{
				$result = (array) $result;
			}
		}

		// Nothing left to do but give up
		else
		{
			throw new \RuntimeException(lang('Fabricator.missingFormatters'));
		}

		// Replace overridden fields
		return array_merge($result, $override);
	}

	/**
	 * Generate an object of faked data
	 *
	 * @param array $override Array of data to add/override
	 *
	 * @return array  An array of faked data
	 *
	 * @throws \RuntimeException
	 */
	protected function makeObject(array $override = [])
	{
		$class = $this->model->returnType;

		// If using the model's fake() method then check it for the correct return type
		if (is_null($this->formatters) && method_exists($this->model, 'fake'))
		{
			$result = $this->model->fake();

			if ($result instanceof $class)
			{
				// Set overrides manually
				foreach ($override as $key => $value)
				{
					$result->{$key} = $value;
				}

				return $result;
			}
		}

		// Get the array values and format them as returnType
		$array  = $this->makeArray($override);
		$object = new $class();

		// Check for the entity method
		if (method_exists($object, 'fill'))
		{
			$object->fill($array);
		}
		else
		{
			foreach ($result as $key => $value)
			{
				$object->{$key} = $value;
			}
		}

		return $object;
	}

	//--------------------------------------------------------------------

	/**
	 * Generate new entities from the database
	 *
	 * @param integer|null $count    Optional number to create a collection
	 * @param array        $override Array of data to add/override
	 * @param boolean      $mock     Whether to execute or mock the insertion
	 *
	 * @return array|object  An array or object (based on returnType), or an array of returnTypes
	 */
	public function create(int $count = null, array $override = [], bool $mock = false)
	{
		// Intercept mock requests
		if ($mock)
		{
			return $this->createMock($count, $override);
		}

		$ids = [];

		// Iterate over new entities and insert each one, storing insert IDs
		foreach ($this->make($count ?? 1, $override) as $result)
		{
			$ids[] = $this->model->insert($row, true);
		}

		return $this->model->find(is_null($count) ? reset($ids) : $ids);
	}

	/**
	 * Generate new database entities without actually inserting them
	 *
	 * @param integer|null $count    Optional number to create a collection
	 * @param array        $override Array of data to add/override
	 *
	 * @return array|object  An array or object (based on returnType), or an array of returnTypes
	 */
	protected function createMock(int $count = null, array $override = [])
	{
		$datetime = $this->model->setDate();

		// Determine which fields we will need
		$fields = [];

		if ($this->model->useTimestamps)
		{
			$fields[$this->model->createdField] = $datetime;
			$fields[$this->model->updatedField] = $datetime;
		}

		if ($this->model->useSoftDeletes)
		{
			$fields[$this->model->deletedField] = $datetime;
		}

		// Iterate over new entities and add the necessary fields
		$return = [];
		foreach ($this->make($count ?? 1, $override) as $i => $result)
		{
			// Set the ID
			$fields[$this->model->primaryKey] = $i;

			// Merge fields
			if (is_array($result))
			{
				$result = array_merge($result, $fields);
			}
			else
			{
				foreach ($fields as $key => $value)
				{
					$result->{$key} = $value;
				}
			}

			$return[] = $result;
		}

		return is_null($count) ? reset($return) : $return;
	}
}
