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

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCURLRequest;
use Config\App;
use Config\CURLRequest as ConfigCURLRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

/**
 * @internal
 */
#[Group('Others')]
final class CURLRequestExceptionTest extends CIUnitTestCase
{
    #[DataProvider('provideFailureResetsSetterOptions')]
    public function testFailureResetsSetterOptions(string $failure, string $setter): void
    {
        $this->configureSharing(false);

        $app     = new App();
        $request = $failure === 'transport'
            ? $this->failingRequest()
            : new MockCURLRequest($app, new URI(), new Response($app));

        $request->setHeader('Authorization', 'Bearer secret');

        match ($setter) {
            'auth'      => $request->setAuth('user', 'password'),
            'body'      => $request->setBody('private body'),
            'form'      => $request->setForm(['private' => 'data']),
            'multipart' => $request->setForm(['private' => 'data'], true),
            'json'      => $request->setJSON(['private' => 'data']),
            default     => throw new InvalidArgumentException('Unknown setter: ' . $setter),
        };

        $options = match ($failure) {
            'header' => ['headers' => ['Authorization' => 'Bearer secret', 'X-Invalid' => "invalid\0value"]],
            'uri'    => ['baseURI' => 'https://private.example:invalid/'],
            'cert'   => ['cert' => 'missing-certificate.pem'],
            'verify' => ['verify' => 'missing-ca-bundle.pem'],
            default  => [],
        };

        $request->setOutput(match ($failure) {
            'response header' => "HTTP/1.1 200 OK\r\nX-Invalid: invalid\0value\r\n\r\nprivate body",
            'response status' => "HTTP/1.1 999 Invalid\r\n\r\nprivate body",
            default           => '',
        });

        $exception = null;

        try {
            $request->post('https://private.example/upload', $options);
        } catch (Throwable $e) {
            $exception = $e;
        }

        $expectedException = in_array($failure, ['header', 'response header'], true)
            ? InvalidArgumentException::class
            : HTTPException::class;

        $this->assertInstanceOf($expectedException, $exception);
        $this->assertNull($request->header('Authorization'));
        $this->assertNull($request->getBody());

        $request->setOutput('');
        $request->get('https://other.example/');

        $options = $request->curl_options;

        $this->assertSame('https://other.example/', $options[CURLOPT_URL]);
        $this->assertArrayNotHasKey(CURLOPT_HTTPHEADER, $options);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $options);
        $this->assertArrayNotHasKey(CURLOPT_USERPWD, $options);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideFailureResetsSetterOptions(): iterable
    {
        foreach (['header', 'uri', 'cert', 'verify', 'transport', 'response header', 'response status'] as $failure) {
            foreach (['auth', 'body', 'form', 'multipart', 'json'] as $setter) {
                yield $failure . ' with ' . $setter => [$failure, $setter];
            }
        }
    }

    #[DataProvider('provideRequestRestoresBaseURIAndDelay')]
    public function testRequestRestoresBaseURIAndDelay(bool $fail, string $defaults): void
    {
        $this->configureSharing(false);

        $baseURI = 'https://default.example/api/?default=value';
        $options = $defaults === 'options' ? ['baseURI' => $baseURI, 'delay' => 100] : [];
        $uri     = $defaults === 'uri' ? new URI($baseURI) : new URI();
        $app     = new App();
        $request = $fail
            ? $this->failingRequest($options, $uri)
            : new MockCURLRequest($app, $uri, new Response($app), $options);

        try {
            $request->get('reports', [
                'baseURI' => 'https://alice@private.example:8443/v2/?api_key=secret#private',
                'delay'   => 200,
            ]);
            $this->assertFalse($fail, 'Expected a transport exception.');
        } catch (HTTPException $e) {
            $this->assertTrue($fail);
            $this->assertStringContainsString('simulated transport failure', $e->getMessage());
        }

        $expectedURI = $defaults === 'none' ? (string) new URI() : $baseURI;
        $this->assertSame($expectedURI, (string) $request->getBaseURI());
        $this->assertEqualsWithDelta($defaults === 'options' ? 0.1 : 0.0, $request->getDelay(), PHP_FLOAT_EPSILON);

        // Mutating the restored URI must not mutate the saved constructor defaults.
        $request->get('reports', ['baseURI' => 'https://bob@private.example:9443/v3/?token=secret']);
        $this->assertSame($expectedURI, (string) $request->getBaseURI());

        $request->get('', ['baseURI' => 'https://other.example/']);

        $expectedURL = $defaults === 'none' ? 'https://other.example/' : 'https://other.example/?default=value';
        $this->assertSame($expectedURL, $request->curl_options[CURLOPT_URL]);
        $this->assertSame($expectedURI, (string) $request->getBaseURI());
    }

    /**
     * @return iterable<string, array{bool, string}>
     */
    public static function provideRequestRestoresBaseURIAndDelay(): iterable
    {
        foreach ([false, true] as $fail) {
            foreach (['none', 'uri', 'options'] as $defaults) {
                yield ($fail ? 'failure' : 'success') . ' with ' . $defaults => [$fail, $defaults];
            }
        }
    }

    #[DataProvider('provideSharingPreservesBaseURIAndDelay')]
    public function testSharingPreservesBaseURIAndDelay(bool $fail): void
    {
        $this->configureSharing(true);

        $app     = new App();
        $request = $fail
            ? $this->failingRequest()
            : new MockCURLRequest($app, new URI(), new Response($app));
        $baseURI = 'https://alice@private.example:8443/v2/?api_key=secret';

        try {
            $request->get('reports', ['baseURI' => $baseURI, 'delay' => 200]);
            $this->assertFalse($fail, 'Expected a transport exception.');
        } catch (HTTPException $e) {
            $this->assertTrue($fail);
            $this->assertStringContainsString('simulated transport failure', $e->getMessage());
        }

        $this->assertSame($baseURI, (string) $request->getBaseURI());
        $this->assertEqualsWithDelta(0.2, $request->getDelay(), PHP_FLOAT_EPSILON);
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public static function provideSharingPreservesBaseURIAndDelay(): iterable
    {
        yield 'success' => [false];

        yield 'failure' => [true];
    }

    public function testTransportFailureResetsRequestOptions(): void
    {
        $this->configureSharing(false);

        $request = $this->failingRequest([
            'headers'    => ['X-Default' => 'configured'],
            'user_agent' => 'DefaultAgent',
        ]);

        try {
            $request->post('https://private.example/upload', [
                'headers' => ['Authorization' => 'Bearer secret'],
                'body'    => 'private body',
                'auth'    => ['user', 'password'],
                'proxy'   => 'http://proxy.example:3128',
            ]);
            $this->fail('Expected a transport exception.');
        } catch (HTTPException $e) {
            $this->assertStringContainsString('simulated transport failure', $e->getMessage());
        }

        $this->assertNull($request->header('Authorization'));
        $this->assertNull($request->getBody());

        $request->get('https://other.example/');

        $options = $request->curl_options;

        $this->assertSame('https://other.example/', $options[CURLOPT_URL]);
        $this->assertSame(['X-Default: configured'], $options[CURLOPT_HTTPHEADER]);
        $this->assertSame('DefaultAgent', $options[CURLOPT_USERAGENT]);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $options);
        $this->assertArrayNotHasKey(CURLOPT_USERPWD, $options);
        $this->assertArrayNotHasKey(CURLOPT_PROXY, $options);
    }

    public function testOptionValidationFailureResetsRequestOptions(): void
    {
        $this->configureSharing(false);

        $app     = new App();
        $request = new MockCURLRequest($app, new URI(), new Response($app));

        try {
            $request->post('https://private.example/upload', [
                'headers' => ['Authorization' => 'Bearer secret'],
                'body'    => 'private body',
                'cert'    => 'missing-certificate.pem',
            ]);
            $this->fail('Expected an invalid certificate exception.');
        } catch (HTTPException $e) {
            $this->assertStringContainsString('missing-certificate.pem', $e->getMessage());
        }

        $this->assertNull($request->header('Authorization'));
        $this->assertNull($request->getBody());

        $request->get('https://other.example/');

        $options = $request->curl_options;

        $this->assertArrayNotHasKey(CURLOPT_HTTPHEADER, $options);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $options);
    }

    public function testTransportFailureKeepsOptionsWhenSharingEnabled(): void
    {
        $this->configureSharing(true);

        $request = $this->failingRequest();

        try {
            $request->post('https://private.example/upload', [
                'headers' => ['Authorization' => 'Bearer secret'],
                'body'    => 'private body',
            ]);
            $this->fail('Expected a transport exception.');
        } catch (HTTPException $e) {
            $this->assertStringContainsString('simulated transport failure', $e->getMessage());
        }

        $request->get('https://other.example/');

        $options = $request->curl_options;

        $this->assertSame(['Authorization: Bearer secret'], $options[CURLOPT_HTTPHEADER]);
        $this->assertSame('private body', $options[CURLOPT_POSTFIELDS]);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function failingRequest(array $options = [], ?URI $uri = null): MockCURLRequest
    {
        $app = new App();

        return new class ($app, $uri ?? new URI(), new Response($app), $options) extends MockCURLRequest {
            private bool $failNextRequest = true;

            protected function sendRequest(array $curlOptions = []): string
            {
                if ($this->failNextRequest) {
                    $this->failNextRequest = false;

                    throw HTTPException::forCurlError('7', 'simulated transport failure');
                }

                return parent::sendRequest($curlOptions);
            }
        };
    }

    private function configureSharing(bool $shareOptions): void
    {
        $config               = new ConfigCURLRequest();
        $config->shareOptions = $shareOptions;

        Factories::injectMock('config', 'CURLRequest', $config);
    }
}
