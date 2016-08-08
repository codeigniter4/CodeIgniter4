<?php namespace CodeIgniter\HTTP;

use Config\App;

final class InflectorHelperTest extends \CIUnitTestCase
{

	public function setUp()
	{
	    helper('inflector');
	}

	//--------------------------------------------------------------------

	public function testSingular()
	{
		$strings = 
		[
			'matrices'  => 'matrix',
			'oxen'      => 'ox',
			'aliases'   => 'alias',
			'octupus'   => 'octupus',
			'shoes'     => 'shoe',
			'buses'     => 'bus',
			'campus'    => 'campus',
			'campuses'  => 'campus',
			'mice'      => 'mouse',
			'movies'    => 'movie',
			'series'    => 'series',
			'hives'     => 'hive',
			'lives'     => 'life',
			'analyses'  => 'analysis',
			'men'       => 'man',
			'people'    => 'person',
			'children'  => 'child',
			'statuses'  => 'status',
			'news'      => 'news',
			'us'        => 'us',
			'tests'     => 'test',
			'queries'   => 'query',
			'dogs'      => 'dog',
			'cats'      => 'cat',
			'families'  => 'family',
			'countries' => 'country'
		];

		foreach ($strings as $pluralizedString => $singularizedString)
		{
			$singular = singular($pluralizedString);
			$this->assertEquals($singular, $singularizedString);
		}
	}

	//--------------------------------------------------------------------

	public function testPlural()
	{
		$strings = 
		[
			'searches'  => 'search',
			'matrices'  => 'matrix',
			'oxen'      => 'ox',
			'aliases'   => 'alias',
			'octupus'   => 'octupus',
			'shoes'     => 'shoe',
			'buses'     => 'bus',
			'mice'      => 'mouse',
			'movies'    => 'movie',
			'series'    => 'series',
			'hives'     => 'hive',
			'lives'     => 'life',
			'analyses'  => 'analysis',
			'men'       => 'man',
			'people'    => 'person',
			'children'  => 'child',
			'statuses'  => 'status',
			'news'      => 'news',
			'us'        => 'us',
			'tests'     => 'test',
			'queries'   => 'query',
			'dogs'      => 'dog',
			'cats'      => 'cat',
			'families'  => 'family',
			'countries' => 'country'
		];

		foreach ($strings as $pluralizedString => $singularizedString)
		{
			$plural = plural($singularizedString);
			$this->assertEquals($plural, $pluralizedString);
		}
	}

	//--------------------------------------------------------------------

	public function testCamelize()
	{
		$strings = 
		[
			'hello from codeIgniter 4' => 'Hello From CodeIgniter 4',
			'hello_world'              => 'Hello World'
		];

		foreach ($strings as $lowerCasedString => $camelizedString)
		{
			$camelized = camelize($lowerCasedString);
			$this->assertEquals($camelized, $camelizedString);
		}
	}

	//--------------------------------------------------------------------

	public function testUnderscore()
	{
		$strings = 
		[
			'Hello From CodeIgniter 4' => 'Hello_From_CodeIgniter_4',
			'hello world'              => 'hello_world'
		];

		foreach ($strings as $lowerCasedString => $camelizedString)
		{
			$underscored = underscore($lowerCasedString);
			$this->assertEquals($underscored, $camelizedString);
		}
	}

	//--------------------------------------------------------------------

	public function testHumanize()
	{
		$underscored = ['Hello_From_CodeIgniter_4', 'Hello From CodeIgniter 4'];
		$dashed      = ['hello-world'             , 'Hello World'];

		$humanizedUnderscore = humanize($underscored[0]);
		$humanizedDash       = humanize($dashed[0], '-');

		$this->assertEquals($humanizedUnderscore, $underscored[1]);
		$this->assertEquals($humanizedDash, $dashed[1]);
	}

	//--------------------------------------------------------------------

	public function testIsCountable()
	{
		$words = 
		[
			'tip'        => 'advice',
			'fight'      => 'bravery',
			'thing'      => 'equipment',
			'deocration' => 'jewelry',
			'line'       => 'series',
			'letter'     => 'spelling'
		];

		foreach ($words as $countable => $unCountable)
		{
			$this->assertEquals(is_countable($countable), true);
			$this->assertEquals(is_countable($unCountable), false);
		}
	}

}