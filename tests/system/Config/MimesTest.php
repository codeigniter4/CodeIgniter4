<?php namespace Config;

class MimesTest extends \CIUnitTestCase
{
	public function extensionsList()
	{
		return [
			'null'      => [
				null,
				'xkadjflkjdsf',
			],
			'single'    => [
				'cpt',
				'application/mac-compactpro',
			],
			'trimmed'   => [
				'cpt',
				' application/mac-compactpro ',
			],
			'manyMimes' => [
				'csv',
				'text/csv',
			],
			'mixedCase' => [
				'csv',
				'text/CSV',
			],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider extensionsList
	 *
	 * @param $expected
	 * @param $ext
	 */
	public function testGuessExtensionFromType($expected, $mime)
	{
		$this->assertEquals($expected, Mimes::guessExtensionFromType($mime));
	}

	//--------------------------------------------------------------------

	public function mimesList()
	{
		return [
			'null'      => [
				null,
				'xalkjdlfkj',
			],
			'single'    => [
				'audio/midi',
				'mid',
			],
			'many'      => [
				'image/bmp',
				'bmp',
			],
			'trimmed'   => [
				'image/bmp',
				'.bmp',
			],
			'mixedCase' => [
				'image/bmp',
				'BMP',
			],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider mimesList
	 */
	public function testGuessTypeFromExtension($expected, $ext)
	{
		$this->assertEquals($expected, Mimes::guessTypeFromExtension($ext));
	}

	//--------------------------------------------------------------------

}
