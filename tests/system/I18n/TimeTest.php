<?php namespace CodeIgniter\I18n;

use IntlDateFormatter;

class TimeTest extends \CIUnitTestCase
{
	public function setUp()
	{
		parent::setUp();

		helper('date');
	}

	public function testNewTimeNow()
	{
		$time = new Time();

		$formatter = new IntlDateFormatter(
			'en_US',
			IntlDateFormatter::SHORT,
			IntlDateFormatter::SHORT,
			'America/Chicago',  // Default for CodeIgniter
			IntlDateFormatter::GREGORIAN
		);

		$this->assertEquals($formatter->format(strtotime('now')), (string)$time);
	}

}
