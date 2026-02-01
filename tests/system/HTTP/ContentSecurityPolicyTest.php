<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * Test the CSP policy directive creation.
 *
 * See https://apimirror.com/http/headers/content-security-policy
 * See https://cspvalidator.org/
 *
 * @internal
 */
#[Group('SeparateProcess')]
final class ContentSecurityPolicyTest extends CIUnitTestCase
{
    private ?Response $response         = null;
    private ?ContentSecurityPolicy $csp = null;

    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();

        $this->prepare();
    }

    private function prepare(bool $CSPEnabled = true): void
    {
        $this->resetServices();

        $config = config(App::class);

        $config->CSPEnabled = $CSPEnabled;

        $this->response = new Response($config);
        $this->response->pretend(false);

        $this->csp = $this->response->getCSP();
    }

    private function work(string $body = 'Hello'): bool
    {
        $this->response->setBody($body);
        $this->response->setCookie('foo', 'bar');

        ob_start();
        $this->response->send();

        return ob_end_clean();
    }

    /**
     * @return list<string>
     */
    private function getCspDirectives(string $header): array
    {
        if (str_starts_with($header, 'Content-Security-Policy-Report-Only:')) {
            $header = trim(substr($header, 36));
        } elseif (str_starts_with($header, 'Content-Security-Policy:')) {
            $header = trim(substr($header, 24));
        }

        return array_map(trim(...), explode(';', $header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testExistence(): void
    {
        $this->assertTrue($this->work());
        $this->assertHeaderEmitted('Content-Security-Policy:');
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testReportOnly(): void
    {
        $this->csp->reportOnly(false);
        $this->assertTrue($this->work());
        $this->assertHeaderEmitted('Content-Security-Policy:');
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testDefaults(): void
    {
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);

        $directives = $this->getCspDirectives($header);
        $this->assertContains("base-uri 'self'", $directives);
        $this->assertContains("connect-src 'self'", $directives);
        $this->assertContains("default-src 'self'", $directives);
        $this->assertContains("img-src 'self'", $directives);
        $this->assertContains("script-src 'self'", $directives);
        $this->assertContains("style-src 'self'", $directives);
    }

    public function testKeywordSourcesAreEnclosedInSingleQuotes(): void
    {
        // clear directives set by config
        $this->csp->clearDirective('child-src');
        $this->csp->clearDirective('form-action');
        $this->csp->clearDirective('img-src');
        $this->csp->clearDirective('object-src');
        $this->csp->clearDirective('script-src');
        $this->csp->clearDirective('style-src');

        $this->csp->addBaseURI('self');
        $this->csp->addChildSrc('none');
        $this->csp->addFontSrc('unsafe-inline');
        $this->csp->addFormAction('unsafe-eval');
        $this->csp->addFrameAncestor('strict-dynamic');
        $this->csp->addFrameSrc('report-sample');
        $this->csp->addImageSrc('wasm-unsafe-eval');
        $this->csp->addMediaSrc('unsafe-allow-redirects');
        $this->csp->addManifestSrc('trusted-types-eval');
        $this->csp->addObjectSrc('report-sha256');
        $this->csp->addScriptSrc('report-sha384');
        $this->csp->addStyleSrc('report-sha512');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);

        $directives = $this->getCspDirectives($header);
        $this->assertContains("base-uri 'self'", $directives);
        $this->assertContains("child-src 'none'", $directives);
        $this->assertContains("font-src 'unsafe-inline'", $directives);
        $this->assertContains("form-action 'unsafe-eval'", $directives);
        $this->assertContains("frame-ancestors 'strict-dynamic'", $directives);
        $this->assertContains("frame-src 'report-sample'", $directives);
        $this->assertContains("img-src 'wasm-unsafe-eval'", $directives);
        $this->assertContains("media-src 'unsafe-allow-redirects'", $directives);
        $this->assertContains("manifest-src 'trusted-types-eval'", $directives);
        $this->assertContains("object-src 'report-sha256'", $directives);
        $this->assertContains("script-src 'report-sha384'", $directives);
        $this->assertContains("style-src 'report-sha512'", $directives);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testConfigSetsListAsDirectivesValues(): void
    {
        $config              = new CSPConfig();
        $config->defaultSrc  = ['self', 'example.com'];
        $config->scriptSrc   = ['self', 'scripts.example.com'];
        $config->styleSrc    = ['self', 'styles.example.com'];
        $config->fontSrc     = ['self', 'fonts.example.com'];
        $config->imageSrc    = ['self', 'images.example.com'];
        $config->connectSrc  = ['self', 'api.example.com'];
        $config->frameSrc    = ['self', 'frames.example.com'];
        $config->childSrc    = ['self', 'childs.example.com'];
        $config->objectSrc   = ['self', 'objects.example.com'];
        $config->mediaSrc    = ['self', 'media.example.com'];
        $config->manifestSrc = ['self', 'manifests.example.com'];
        $config->pluginTypes = ['application/x-shockwave-flash', 'application/pdf'];

        $csp = new ContentSecurityPolicy($config);

        $response = new Response(new App());
        $response->pretend(true);

        $response->setBody('Blah blah blah blah');
        $csp->finalize($response);

        $directives = $this->getCspDirectives($response->getHeaderLine('Content-Security-Policy'));
        $this->assertContains("default-src 'self' example.com", $directives);
        $this->assertContains("script-src 'self' scripts.example.com", $directives);
        $this->assertContains("style-src 'self' styles.example.com", $directives);
        $this->assertContains("font-src 'self' fonts.example.com", $directives);
        $this->assertContains("img-src 'self' images.example.com", $directives);
        $this->assertContains("connect-src 'self' api.example.com", $directives);
        $this->assertContains("frame-src 'self' frames.example.com", $directives);
        $this->assertContains("child-src 'self' childs.example.com", $directives);
        $this->assertContains("object-src 'self' objects.example.com", $directives);
        $this->assertContains("media-src 'self' media.example.com", $directives);
        $this->assertContains("manifest-src 'self' manifests.example.com", $directives);
        $this->assertContains('plugin-types application/x-shockwave-flash application/pdf', $directives);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testChildSrc(): void
    {
        $this->csp->addChildSrc('evil.com', true);
        $this->csp->addChildSrc('good.com', false);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("child-src 'self' good.com", $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('child-src evil.com', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testConnectSrc(): void
    {
        $this->csp->reportOnly(true);
        $this->csp->addConnectSrc('iffy.com');
        $this->csp->addConnectSrc('maybe.com');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains("connect-src 'self' iffy.com maybe.com", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testFontSrc(): void
    {
        $this->csp->reportOnly(true);
        $this->csp->addFontSrc('iffy.com');
        $this->csp->addFontSrc('fontsrus.com', false);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('font-src iffy.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('font-src fontsrus.com', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testFormAction(): void
    {
        $this->csp->reportOnly(true);
        $this->csp->addFormAction('surveysrus.com');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains("form-action 'self' surveysrus.com", $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertNotContains("form-action 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testFrameAncestor(): void
    {
        $this->csp->addFrameAncestor('self');
        $this->csp->addFrameAncestor('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('frame-ancestors them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("frame-ancestors 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testFrameSrc(): void
    {
        $this->csp->addFrameSrc('self');
        $this->csp->addFrameSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('frame-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("frame-src 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testImageSrc(): void
    {
        $this->csp->addImageSrc('cdn.cloudy.com');
        $this->csp->addImageSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('img-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("img-src 'self' cdn.cloudy.com", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testMediaSrc(): void
    {
        $this->csp->addMediaSrc('self');
        $this->csp->addMediaSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('media-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("media-src 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testManifestSrc(): void
    {
        $this->csp->addManifestSrc('cdn.cloudy.com');
        $this->csp->addManifestSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('manifest-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('manifest-src cdn.cloudy.com', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testPluginType(): void
    {
        $this->csp->addPluginType('self');
        $this->csp->addPluginType('application/x-shockwave-flash', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('plugin-types application/x-shockwave-flash', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("plugin-types 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testPluginArray(): void
    {
        $this->csp->addPluginType('application/x-shockwave-flash');
        $this->csp->addPluginType('application/wacky-hacky');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('plugin-types application/x-shockwave-flash application/wacky-hacky', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testObjectSrc(): void
    {
        $this->csp->addObjectSrc('cdn.cloudy.com');
        $this->csp->addObjectSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('object-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("object-src 'self' cdn.cloudy.com", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testScriptSrc(): void
    {
        $this->csp->addScriptSrc('cdn.cloudy.com');
        $this->csp->addScriptSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('script-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("script-src 'self' cdn.cloudy.com", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testStyleSrc(): void
    {
        $this->csp->addStyleSrc('cdn.cloudy.com');
        $this->csp->addStyleSrc('them.com', true);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
        $this->assertIsString($header);
        $this->assertContains('style-src them.com', $this->getCspDirectives($header));

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("style-src 'self' cdn.cloudy.com", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBaseURIDefault(): void
    {
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("base-uri 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBaseURI(): void
    {
        $this->csp->addBaseURI('example.com');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('base-uri example.com', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBaseURIRich(): void
    {
        $this->csp->addBaseURI(['self', 'example.com']);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains("base-uri 'self' example.com", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testDefaultSrc(): void
    {
        $this->csp->reportOnly(false);
        $this->csp->setDefaultSrc('maybe.com');
        $this->csp->setDefaultSrc('iffy.com');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('default-src iffy.com', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testReportURI(): void
    {
        $this->csp->reportOnly(false);
        $this->csp->setReportURI('http://example.com/csptracker');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('report-uri http://example.com/csptracker', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testRemoveReportURI(): void
    {
        $this->csp->reportOnly(false);
        $this->csp->setReportURI('');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertStringNotContainsString('report-uri', $header);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testSandboxEmptyFlag(): void
    {
        $this->csp->addSandbox('');
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('sandbox', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testSandboxFlags(): void
    {
        $this->csp->reportOnly(false);
        $this->csp->addSandbox(['allow-popups', 'allow-top-navigation']);
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('sandbox allow-popups allow-top-navigation', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testUpgradeInsecureRequests(): void
    {
        $this->csp->upgradeInsecureRequests();
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertContains('upgrade-insecure-requests', $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBodyEmpty(): void
    {
        $this->response->setBody('');
        $this->csp->finalize($this->response);
        $this->assertSame('', $this->response->getBody());
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBodyScriptNonce(): void
    {
        $body = 'Blah blah {csp-script-nonce} blah blah';

        $this->response->setBody($body);
        $this->csp->addScriptSrc('cdn.cloudy.com');
        $this->assertTrue($this->work($body));

        $nonceStyle = array_filter(
            $this->getPrivateProperty($this->csp, 'styleSrc'),
            static fn ($value): bool => str_starts_with($value, 'nonce-'),
            ARRAY_FILTER_USE_KEY,
        );
        $this->assertSame([], $nonceStyle);

        $responseBody = $this->response->getBody();
        $this->assertIsString($responseBody);
        $this->assertStringContainsString('nonce=', $responseBody);

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertStringContainsString('nonce-', $header);
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

        $this->assertStringContainsString('nonce=', (string) $response->getBody());
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

        $this->assertStringContainsString('{csp-script-nonce}', (string) $response->getBody());

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

        $this->assertStringContainsString('{csp-style-nonce}', (string) $response->getBody());

        $result = new TestResponse($response);
        $result->assertHeader('Content-Security-Policy');
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBodyStyleNonce(): void
    {
        $body = 'Blah blah {csp-style-nonce} blah blah';
        $this->response->setBody($body);
        $this->csp->addStyleSrc('cdn.cloudy.com');
        $this->assertTrue($this->work($body));

        $nonceScript = array_filter(
            $this->getPrivateProperty($this->csp, 'scriptSrc'),
            static fn ($value): bool => str_starts_with($value, 'nonce-'),
            ARRAY_FILTER_USE_KEY,
        );
        $this->assertSame([], $nonceScript);

        $responseBody = $this->response->getBody();
        $this->assertIsString($responseBody);
        $this->assertStringContainsString('nonce=', $responseBody);

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertStringContainsString('nonce-', $header);
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

        $this->assertStringContainsString('nonce=', (string) $response->getBody());
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testHeaderWrongCaseNotFound(): void
    {
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('content-security-policy');
        $this->assertNull($header);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testHeaderIgnoreCase(): void
    {
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('content-security-policy', true);
        $this->assertIsString($header);
        $this->assertContains("base-uri 'self'", $this->getCspDirectives($header));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testCSPDisabled(): void
    {
        $this->prepare(false);
        $this->assertTrue($this->work());

        $this->response->getCSP()->addStyleSrc('https://example.com');
        $this->assertHeaderNotEmitted('content-security-policy', true);
    }

    public function testGetScriptNonce(): void
    {
        $nonce = $this->csp->getScriptNonce();
        $this->assertMatchesRegularExpression('/\A[0-9a-z]{24}\z/', $nonce);
    }

    public function testGetStyleNonce(): void
    {
        $nonce = $this->csp->getStyleNonce();
        $this->assertMatchesRegularExpression('/\A[0-9a-z]{24}\z/', $nonce);
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testHeaderScriptNonceEmittedOnceGetScriptNonceCalled(): void
    {
        $this->csp->getScriptNonce();
        $this->assertTrue($this->work());

        $header = $this->getHeaderEmitted('Content-Security-Policy');
        $this->assertIsString($header);
        $this->assertStringContainsString("script-src 'self' 'nonce-", $header);
    }

    public function testClearDirective(): void
    {
        $this->csp->addFontSrc('fonts.example.com');
        $this->csp->addStyleSrc('css.example.com');
        $this->csp->setReportURI('http://example.com/csp/reports');

        $this->csp->clearDirective('fonts-src'); // intentional wrong directive
        $this->csp->clearDirective('style-src');
        $this->csp->clearDirective('report-uri');

        $this->csp->finalize($this->response);

        $header = $this->response->getHeaderLine('Content-Security-Policy');

        $directives = $this->getCspDirectives($header);
        $this->assertContains('font-src fonts.example.com', $directives);
        $this->assertNotContains('style-src css.example.com', $directives);
        $this->assertNotContains('report-uri http://example.com/csp/reports', $directives);
    }
}
