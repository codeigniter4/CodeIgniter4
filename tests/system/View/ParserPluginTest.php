<?php

namespace CodeIgniter\View;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Validation;
use Config\Services;

/**
 * @internal
 */
final class ParserPluginTest extends CIUnitTestCase
{
    /**
     * @var Parser
     */
    protected $parser;
    /**
     * @var Validation
     */
    protected $validator;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        $this->parser    = Services::parser();
        $this->validator = Services::validation();
    }

    public function testCurrentURL()
    {
        helper('url');
        $template = '{+ current_url +}';

        $this->assertSame(current_url(), $this->parser->renderString($template));
    }

    public function testPreviousURL()
    {
        helper('url');
        $template = '{+ previous_url +}';

        // Ensure a previous URL exists to work with.
        $_SESSION['_ci_previous_url'] = 'http://example.com/foo';

        $this->assertSame(previous_url(), $this->parser->renderString($template));
    }

    public function testMailto()
    {
        helper('url');
        $template = '{+ mailto email=foo@example.com title=Silly +}';

        $this->assertSame(mailto('foo@example.com', 'Silly'), $this->parser->renderString($template));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3523
     */
    public function testMailtoWithDashAndParenthesis()
    {
        helper('url');
        $template = '{+ mailto email=foo-bar@example.com title="Scilly (the Great)" +}';

        $this->assertSame(mailto('foo-bar@example.com', 'Scilly (the Great)'), $this->parser->renderString($template));
    }

    public function testSafeMailto()
    {
        helper('url');
        $template = '{+ safe_mailto email=foo@example.com title=Silly +}';

        $this->assertSame(safe_mailto('foo@example.com', 'Silly'), $this->parser->renderString($template));
    }

    public function testLang()
    {
        $template = '{+ lang Number.terabyteAbbr +}';

        $this->assertSame('TB', $this->parser->renderString($template));
    }

    public function testValidationErrors()
    {
        $this->validator->setError('email', 'Invalid email address');

        $template = '{+ validation_errors field=email +}';

        $this->assertSame($this->setHints($this->validator->showError('email')), $this->setHints($this->parser->renderString($template)));
    }

    public function testRoute()
    {
        // prime the pump
        $routes = service('routes');
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $template = '{+ route myController::goto string 13 +}';

        $this->assertSame('/path/string/to/13', $this->parser->renderString($template));
    }

    public function testSiteURL()
    {
        $template = '{+ siteURL +}';

        $this->assertSame('http://example.com/index.php', $this->parser->renderString($template));
    }

    public function testValidationErrorsList()
    {
        $this->validator->setError('email', 'Invalid email address');
        $this->validator->setError('username', 'User name must be unique');
        $template = '{+ validation_errors +}';

        $this->assertSame($this->setHints($this->validator->listErrors()), $this->setHints($this->parser->renderString($template)));
    }

    public function setHints($output)
    {
        return preg_replace('/(<!-- DEBUG-VIEW+) (\w+) (\d+)/', '${1}', $output);
    }
}
