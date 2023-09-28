<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Validation;
use Config\Services;

/**
 * @internal
 *
 * @group Others
 */
final class ParserPluginTest extends CIUnitTestCase
{
    private Parser $parser;
    private Validation $validator;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        $this->parser    = Services::parser();
        $this->validator = Services::validation();
    }

    public function testCurrentURL(): void
    {
        $template = '{+ current_url +}';

        $this->assertSame(current_url(), $this->parser->renderString($template));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @group SeparateProcess
     */
    public function testPreviousURL(): void
    {
        $template = '{+ previous_url +}';

        // Ensure a previous URL exists to work with.
        session()->set('_ci_previous_url', 'http://example.com/foo');

        $this->assertSame(previous_url(), $this->parser->renderString($template));
    }

    public function testMailto(): void
    {
        $template = '{+ mailto email=foo@example.com title=Silly +}';

        $this->assertSame(mailto('foo@example.com', 'Silly'), $this->parser->renderString($template));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/3523
     */
    public function testMailtoWithDashAndParenthesis(): void
    {
        $template = '{+ mailto email=foo-bar@example.com title="Online español test level" +}';

        $this->assertSame(mailto('foo-bar@example.com', 'Online español test level'), $this->parser->renderString($template));
    }

    public function testSafeMailto(): void
    {
        $template = '{+ safe_mailto email=foo@example.com title=Silly +}';

        $this->assertSame(safe_mailto('foo@example.com', 'Silly'), $this->parser->renderString($template));
    }

    public function testLang(): void
    {
        $template = '{+ lang Number.terabyteAbbr +}';

        $this->assertSame('TB', $this->parser->renderString($template));
    }

    public function testValidationErrors(): void
    {
        $this->validator->setError('email', 'Invalid email address');

        $template = '{+ validation_errors field=email +}';

        $this->assertSame($this->setHints($this->validator->showError('email')), $this->setHints($this->parser->renderString($template)));
    }

    public function testRoute(): void
    {
        // prime the pump
        $routes = service('routes');
        // @TODO Do not put any placeholder after (:any).
        //       Because the number of parameters passed to the controller method may change.
        $routes->add('path/(:any)/to/(:num)', 'myController::goto/$1/$2');

        $template = '{+ route myController::goto string 13 +}';

        $this->assertSame('/path/string/to/13', $this->parser->renderString($template));
    }

    public function testSiteURL(): void
    {
        $template = '{+ siteURL +}';

        $this->assertSame('http://example.com/index.php', $this->parser->renderString($template));
    }

    public function testValidationErrorsList(): void
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

    public function testCspScriptNonceWithCspEnabled(): void
    {
        $config             = config('App');
        $config->CSPEnabled = true;

        $template = 'aaa {+ csp_script_nonce +} bbb';

        $this->assertMatchesRegularExpression(
            '/aaa nonce="[0-9a-z]{24}" bbb/',
            $this->parser->renderString($template)
        );
    }
}
