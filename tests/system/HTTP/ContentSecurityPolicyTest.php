<?php
namespace CodeIgniter\HTTP;

use Config\App;

/**
 * Test the CSP policy directive creation.
 *
 * See https://apimirror.com/http/headers/content-security-policy
 * See https://cspvalidator.org/
 */
class ContentSecurityPolicyTest extends \CIUnitTestCase
{

	// Having this method as setUp() doesn't work - can't find Config\App !?
	protected function prepare()
	{
		$config             = new App();
		$config->CSPEnabled = true;
		$this->response     = new Response($config);
		$this->response->pretend(false);
		$this->csp = $this->response->CSP;
	}

	protected function work(string $parm = 'Hello')
	{
		$body = $parm;
		$this->response->setBody($body);
		$this->response->setCookie('foo', 'bar');

		ob_start();
		$this->response->send();
		$buffer = ob_clean();
		if (ob_get_level() > 0)
		{
			ob_end_clean();
		}
		return $buffer;
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testExistence()
	{
		$this->prepare();
		$result = $this->work();

		$this->assertHeaderEmitted('Content-Security-Policy:');
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testReportOnly()
	{
		$this->prepare();
		$this->csp->reportOnly(false);
		$result = $this->work();

		$this->assertHeaderEmitted('Content-Security-Policy:');
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testDefaults()
	{
		$this->prepare();

		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("base-uri 'self';", $result);
		$this->assertContains("connect-src 'self';", $result);
		$this->assertContains("default-src 'self';", $result);
		$this->assertContains("img-src 'self';", $result);
		$this->assertContains("script-src 'self';", $result);
		$this->assertContains("style-src 'self';", $result);
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testChildSrc()
	{
		$this->prepare();
		$this->csp->addChildSrc('evil.com', true);
		$this->csp->addChildSrc('good.com', false);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('child-src evil.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("child-src 'self' good.com;", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testConnectSrc()
	{
		$this->prepare();
		$this->csp->reportOnly(true);
		$this->csp->addConnectSrc('iffy.com');
		$this->csp->addConnectSrc('maybe.com');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('connect-src iffy.com maybe.com;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testFontSrc()
	{
		$this->prepare();
		$this->csp->reportOnly(true);
		$this->csp->addFontSrc('iffy.com');
		$this->csp->addFontSrc('fontsrus.com', false);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('font-src iffy.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('font-src fontsrus.com;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testFormAction()
	{
		$this->prepare();
		$this->csp->reportOnly(true);
		$this->csp->addFormAction('surveysrus.com');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('form-action surveysrus.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("form-action 'self';", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testFrameAncestor()
	{
		$this->prepare();
		$this->csp->addFrameAncestor('self');
		$this->csp->addFrameAncestor('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('frame-ancestors them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("frame-ancestors 'self';", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testImageSrc()
	{
		$this->prepare();
		$this->csp->addImageSrc('cdn.cloudy.com');
		$this->csp->addImageSrc('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('img-src them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("img-src 'self' cdn.cloudy.com;", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testMediaSrc()
	{
		$this->prepare();
		$this->csp->addMediaSrc('self');
		$this->csp->addMediaSrc('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('media-src them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("media-src 'self';", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testManifestSrc()
	{
		$this->prepare();
		$this->csp->addManifestSrc('cdn.cloudy.com');
		$this->csp->addManifestSrc('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('manifest-src them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('manifest-src cdn.cloudy.com;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testPluginType()
	{
		$this->prepare();
		$this->csp->addPluginType('self');
		$this->csp->addPluginType('application/x-shockwave-flash', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('plugin-types application/x-shockwave-flash;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("plugin-types 'self';", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testPluginArray()
	{
		$this->prepare();
		$this->csp->addPluginType('application/x-shockwave-flash');
		$this->csp->addPluginType('application/wacky-hacky');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('plugin-types application/x-shockwave-flash application/wacky-hacky;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testObjectSrc()
	{
		$this->prepare();
		$this->csp->addObjectSrc('cdn.cloudy.com');
		$this->csp->addObjectSrc('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('object-src them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("object-src 'self' cdn.cloudy.com;", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testScriptSrc()
	{
		$this->prepare();
		$this->csp->addScriptSrc('cdn.cloudy.com');
		$this->csp->addScriptSrc('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('script-src them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("script-src 'self' cdn.cloudy.com;", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testStyleSrc()
	{
		$this->prepare();
		$this->csp->addStyleSrc('cdn.cloudy.com');
		$this->csp->addStyleSrc('them.com', true);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy-Report-Only');
		$this->assertContains('style-src them.com;', $result);
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("style-src 'self' cdn.cloudy.com;", $result);
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testBaseURIDefault()
	{
		$this->prepare();
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("base-uri 'self';", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testBaseURI()
	{
		$this->prepare();
		$this->csp->addBaseURI('example.com');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('base-uri example.com;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testBaseURIRich()
	{
		$this->prepare();
		$this->csp->addBaseURI(['self', 'example.com']);
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains("base-uri 'self' example.com;", $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testDefaultSrc()
	{
		$this->prepare();
		$this->csp->reportOnly(false);
		$this->csp->setDefaultSrc('maybe.com');
		$this->csp->setDefaultSrc('iffy.com');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('default-src iffy.com;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testReportURI()
	{
		$this->prepare();
		$this->csp->reportOnly(false);
		$this->csp->setReportURI('http://example.com/csptracker');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('report-uri http://example.com/csptracker;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testSandboxFlags()
	{
		$this->prepare();
		$this->csp->reportOnly(false);
		$this->csp->addSandbox(['allow-popups', 'allow-top-navigation']);
		//      $this->csp->addSandbox('allow-popups');
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('sandbox allow-popups allow-top-navigation;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testUpgradeInsecureRequests()
	{
		$this->prepare();
		$this->csp->upgradeInsecureRequests();
		$result = $this->work();

		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('upgrade-insecure-requests;', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testBodyEmpty()
	{
		$this->prepare();
		$body = '';
		$this->response->setBody($body);
		$this->csp->finalize($this->response);
		$this->assertEquals($body, $this->response->getBody());
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testBodyScriptNonce()
	{
		$this->prepare();
		$body = 'Blah blah {csp-script-nonce} blah blah';
		$this->response->setBody($body);
		$this->csp->addScriptSrc('cdn.cloudy.com');

		$result = $this->work($body);

		$this->assertContains('nonce=', $this->response->getBody());
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('nonce-', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testBodyStyleNonce()
	{
		$this->prepare();
		$body = 'Blah blah {csp-style-nonce} blah blah';
		$this->response->setBody($body);
		$this->csp->addStyleSrc('cdn.cloudy.com');

		$result = $this->work($body);

		$this->assertContains('nonce=', $this->response->getBody());
		$result = $this->getHeaderEmitted('Content-Security-Policy');
		$this->assertContains('nonce-', $result);
	}

	//--------------------------------------------------------------------

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testHeaderWrongCaseNotFound()
	{
		$this->prepare();
		$result = $this->work();

		$result = $this->getHeaderEmitted('content-security-policy');
		$this->assertNull($result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testHeaderIgnoreCase()
	{
		$this->prepare();
		$result = $this->work();

		$result = $this->getHeaderEmitted('content-security-policy', true);
		$this->assertContains("base-uri 'self';", $result);
	}

}
