<?php

use CodeIgniter\View\Parser;

class ParserPluginTest extends \CIUnitTestCase
{
	protected $parser;
	protected $validator;

	public function setUp()
	{
		parent::setUp();

		$this->parser = \Config\Services::parser();
		$this->validator = \Config\Services::validation();
	}

	public function testCurrentURL()
	{
		helper('url');
		$template = "{+ current_url +}";

		$this->assertEquals(current_url(), $this->parser->renderString($template));
	}

	public function testPreviousURL()
	{
		helper('url');
		$template = "{+ previous_url +}";

		// Ensure a previous URL exists to work with.
		$_SESSION['_ci_previous_url'] = 'http://example.com/foo';

		$this->assertEquals(previous_url(), $this->parser->renderString($template));
	}

	public function testMailto()
	{
		helper('url');
		$template = '{+ mailto email=foo@example.com title=Silly +}';

		$this->assertEquals(mailto('foo@example.com', 'Silly'), $this->parser->renderString($template));
	}

	public function testSafeMailto()
	{
		helper('url');
		$template = '{+ safe_mailto email=foo@example.com title=Silly +}';

		$this->assertEquals(safe_mailto('foo@example.com', 'Silly'), $this->parser->renderString($template));
	}

	public function testLang()
	{
		$template = '{+ lang Number.terabyteAbbr +}';

		$this->assertEquals('TB', $this->parser->renderString($template));
	}

	public function testValidationErrors()
	{
		
		$this->validator->setError("email","Invalid email address");
		
		$template = '{+ validation_errors field=email +}';

		$this->assertEquals($this->validator->showError('email'), $this->parser->renderString($template));
	}

	public function testValidationErrorsList()
	{
		
		$this->validator->setError("email","Invalid email address");
		$this->validator->setError("username","User name must be unique");
		$template = '{+ validation_errors +}';

		$this->assertEquals($this->validator->listErrors(), $this->parser->renderString($template));
	}

}
