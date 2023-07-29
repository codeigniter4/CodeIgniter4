<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestResponse;
use Config\App;
use Config\ContentSecurityPolicy as CSPConfig;

/**
 * Test the CSP policy directive creation.
 *
 * See https://apimirror.com/http/headers/content-security-policy
 * See https://cspvalidator.org/
 *
 * @internal
 *
 * @group SeparateProcess
 */
final class ContentSecurityPolicyTest extends CIUnitTestCase
{
    private ?Response $response         = null;
    private ?ContentSecurityPolicy $csp = null;

    // Having this method as setUp() doesn't work - can't find Config\App !?
    protected function prepare(bool $CSPEnabled = true): void
    {
        $this->resetServices();

        $config             = config('App');
        $config->CSPEnabled = $CSPEnabled;
        $this->response     = new Response($config);
        $this->response->pretend(false);
        $this->csp = $this->response->getCSP();
    }

    protected function work(string $parm = 'Hello')
    {
        $body = $parm;
        $this->response->setBody($body);
        $this->response->setCookie('foo', 'bar');

        ob_start();
        $this->response->send();
        $buffer = ob_clean();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        return $buffer;
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testExistence(): void
    {
        $this->prepare();
        $this->work();

        $this->assertHeaderEmitted('Content-Security-Policy:');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReportOnly(): void
    {
        $this->prepare();
        $this->csp->reportOnly(false);
        $this->work();

        $this->assertHeaderEmitted('Content-Security-Policy:');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDefaults(): void
    {
        $this->prepare();

        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("base-uri 'self';", $result);
        $this->assertStringContainsString("connect-src 'self';", $result);
        $this->assertStringContainsString("default-src 'self';", $result);
        $this->assertStringContainsString("img-src 'self';", $result);
        $this->assertStringContainsString("script-src 'self';", $result);
        $this->assertStringContainsString("style-src 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testChildSrc(): void
    {
        $this->prepare();
        $this->csp->addChildSrc('evil.com', true);
        $this->csp->addChildSrc('good.com', false);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('child-src evil.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("child-src 'self' good.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConnectSrc(): void
    {
        $this->prepare();
        $this->csp->reportOnly(true);
        $this->csp->addConnectSrc('iffy.com');
        $this->csp->addConnectSrc('maybe.com');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString("connect-src 'self' iffy.com maybe.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFontSrc(): void
    {
        $this->prepare();
        $this->csp->reportOnly(true);
        $this->csp->addFontSrc('iffy.com');
        $this->csp->addFontSrc('fontsrus.com', false);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('font-src iffy.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('font-src fontsrus.com;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFormAction(): void
    {
        $this->prepare();
        $this->csp->reportOnly(true);
        $this->csp->addFormAction('surveysrus.com');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString("form-action 'self' surveysrus.com;", $result);

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringNotContainsString("form-action 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFrameAncestor(): void
    {
        $this->prepare();
        $this->csp->addFrameAncestor('self');
        $this->csp->addFrameAncestor('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('frame-ancestors them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFrameSrc(): void
    {
        $this->prepare();
        $this->csp->addFrameSrc('self');
        $this->csp->addFrameSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('frame-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("frame-src 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testImageSrc(): void
    {
        $this->prepare();
        $this->csp->addImageSrc('cdn.cloudy.com');
        $this->csp->addImageSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('img-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("img-src 'self' cdn.cloudy.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMediaSrc(): void
    {
        $this->prepare();
        $this->csp->addMediaSrc('self');
        $this->csp->addMediaSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('media-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("media-src 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testManifestSrc(): void
    {
        $this->prepare();
        $this->csp->addManifestSrc('cdn.cloudy.com');
        $this->csp->addManifestSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('manifest-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('manifest-src cdn.cloudy.com;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPluginType(): void
    {
        $this->prepare();
        $this->csp->addPluginType('self');
        $this->csp->addPluginType('application/x-shockwave-flash', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('plugin-types application/x-shockwave-flash;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("plugin-types 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPluginArray(): void
    {
        $this->prepare();
        $this->csp->addPluginType('application/x-shockwave-flash');
        $this->csp->addPluginType('application/wacky-hacky');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('plugin-types application/x-shockwave-flash application/wacky-hacky;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testObjectSrc(): void
    {
        $this->prepare();
        $this->csp->addObjectSrc('cdn.cloudy.com');
        $this->csp->addObjectSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('object-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("object-src 'self' cdn.cloudy.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testScriptSrc(): void
    {
        $this->prepare();
        $this->csp->addScriptSrc('cdn.cloudy.com');
        $this->csp->addScriptSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('script-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self' cdn.cloudy.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStyleSrc(): void
    {
        $this->prepare();
        $this->csp->addStyleSrc('cdn.cloudy.com');
        $this->csp->addStyleSrc('them.com', true);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertStringContainsString('style-src them.com;', $result);
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("style-src 'self' cdn.cloudy.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBaseURIDefault(): void
    {
        $this->prepare();
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("base-uri 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBaseURI(): void
    {
        $this->prepare();
        $this->csp->addBaseURI('example.com');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('base-uri example.com;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBaseURIRich(): void
    {
        $this->prepare();
        $this->csp->addBaseURI(['self', 'example.com']);
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("base-uri 'self' example.com;", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDefaultSrc(): void
    {
        $this->prepare();
        $this->csp->reportOnly(false);
        $this->csp->setDefaultSrc('maybe.com');
        $this->csp->setDefaultSrc('iffy.com');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('default-src iffy.com;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReportURI(): void
    {
        $this->prepare();
        $this->csp->reportOnly(false);
        $this->csp->setReportURI('http://example.com/csptracker');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('report-uri http://example.com/csptracker;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSandboxFlags(): void
    {
        $this->prepare();
        $this->csp->reportOnly(false);
        $this->csp->addSandbox(['allow-popups', 'allow-top-navigation']);
        //      $this->csp->addSandbox('allow-popups');
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('sandbox allow-popups allow-top-navigation;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUpgradeInsecureRequests(): void
    {
        $this->prepare();
        $this->csp->upgradeInsecureRequests();
        $result = $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('upgrade-insecure-requests;', $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBodyEmpty(): void
    {
        $this->prepare();
        $body = '';
        $this->response->setBody($body);
        $this->csp->finalize($this->response);
        $this->assertSame($body, $this->response->getBody());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBodyScriptNonce(): void
    {
        $this->prepare();
        $body = 'Blah blah {csp-script-nonce} blah blah';
        $this->response->setBody($body);
        $this->csp->addScriptSrc('cdn.cloudy.com');

        $result     = $this->work($body);
        $nonceStyle = array_filter(
            $this->getPrivateProperty($this->csp, 'styleSrc'),
            static fn ($value) => strpos($value, 'nonce-') === 0
        );

        $this->assertStringContainsString('nonce=', $this->response->getBody());
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('nonce-', $result);
        $this->assertSame([], $nonceStyle);
    }

    public function testBodyScriptNonceCustomScriptTag(): void
    {
        $config                 = new CSPConfig();
        $config->scriptNonceTag = '{custom-script-nonce-tag}';
        $csp                    = new ContentSecurityPolicy($config);

        $response = new Response(new App());
        $response->pretend(true);
        $body = 'Blah blah {custom-script-nonce-tag} blah blah';
        $response->setBody($body);

        $csp->finalize($response);

        $this->assertStringContainsString('nonce=', $response->getBody());
    }

    public function testBodyScriptNonceDisableAutoNonce(): void
    {
        $config            = new CSPConfig();
        $config->autoNonce = false;
        $csp               = new ContentSecurityPolicy($config);

        $response = new Response(new App());
        $response->pretend(true);
        $body = 'Blah blah {csp-script-nonce} blah blah';
        $response->setBody($body);

        $csp->finalize($response);

        $this->assertStringContainsString('{csp-script-nonce}', $response->getBody());

        $result = new TestResponse($response);
        $result->assertHeader('Content-Security-Policy');
    }

    public function testBodyStyleNonceDisableAutoNonce(): void
    {
        $config            = new CSPConfig();
        $config->autoNonce = false;
        $csp               = new ContentSecurityPolicy($config);

        $response = new Response(new App());
        $response->pretend(true);
        $body = 'Blah blah {csp-style-nonce} blah blah';
        $response->setBody($body);

        $csp->finalize($response);

        $this->assertStringContainsString('{csp-style-nonce}', $response->getBody());

        $result = new TestResponse($response);
        $result->assertHeader('Content-Security-Policy');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBodyStyleNonce(): void
    {
        $this->prepare();
        $body = 'Blah blah {csp-style-nonce} blah blah';
        $this->response->setBody($body);
        $this->csp->addStyleSrc('cdn.cloudy.com');

        $result      = $this->work($body);
        $nonceScript = array_filter(
            $this->getPrivateProperty($this->csp, 'scriptSrc'),
            static fn ($value) => strpos($value, 'nonce-') === 0
        );

        $this->assertStringContainsString('nonce=', $this->response->getBody());
        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString('nonce-', $result);
        $this->assertSame([], $nonceScript);
    }

    public function testBodyStyleNonceCustomStyleTag(): void
    {
        $config                = new CSPConfig();
        $config->styleNonceTag = '{custom-style-nonce-tag}';
        $csp                   = new ContentSecurityPolicy($config);

        $response = new Response(new App());
        $response->pretend(true);
        $body = 'Blah blah {custom-style-nonce-tag} blah blah';
        $response->setBody($body);

        $csp->finalize($response);

        $this->assertStringContainsString('nonce=', $response->getBody());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaderWrongCaseNotFound(): void
    {
        $this->prepare();
        $result = $this->work();

        $result = $this->getHeaderEmitted('content-security-policy');
        $this->assertNull($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaderIgnoreCase(): void
    {
        $this->prepare();
        $result = $this->work();

        $result = $this->getHeaderEmitted('content-security-policy', true);
        $this->assertStringContainsString("base-uri 'self';", $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCSPDisabled(): void
    {
        $this->prepare(false);
        $this->work();
        $this->response->getCSP()->addStyleSrc('https://example.com');

        $this->assertHeaderNotEmitted('content-security-policy', true);
    }

    public function testGetScriptNonce(): void
    {
        $this->prepare();

        $nonce = $this->csp->getScriptNonce();

        $this->assertMatchesRegularExpression('/\A[0-9a-z]{24}\z/', $nonce);
    }

    public function testGetStyleNonce(): void
    {
        $this->prepare();

        $nonce = $this->csp->getStyleNonce();

        $this->assertMatchesRegularExpression('/\A[0-9a-z]{24}\z/', $nonce);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHeaderScriptNonceEmittedOnceGetScriptNonceCalled(): void
    {
        $this->prepare();

        $this->csp->getScriptNonce();
        $this->work();

        $result = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertStringContainsString("script-src 'self' 'nonce-", $result);
    }
}
