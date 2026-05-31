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

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Input\InputData;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('SeparateProcess')]
final class RequestInputTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals([], [], [], [], [], []));
    }

    private function createRequest(?App $config = null, ?string $body = null): IncomingRequest
    {
        $config ??= new App();

        return new IncomingRequest(
            $config,
            new SiteURI($config, ''),
            $body,
            new UserAgent(),
        );
    }

    public function testInputReturnsRequestInput(): void
    {
        $request = $this->createRequest();
        $input   = $request->input();

        $this->assertInstanceOf(RequestInput::class, $input);
    }

    public function testInputReturnsSameRequestInputInstance(): void
    {
        $request = $this->createRequest();

        $this->assertSame($request->input(), $request->input());
    }

    public function testClonedRequestGetsNewRequestInputInstance(): void
    {
        $request = $this->createRequest();
        $input   = $request->input();

        $clonedRequest = $request->withMethod(Method::POST);

        $this->assertNotSame($input, $clonedRequest->input());
    }

    public function testGetReadsGetData(): void
    {
        service('superglobals')->setGet('page', '3');
        service('superglobals')->setGet('filters', ['active' => 'true']);
        service('superglobals')->setPost('page', '10');

        $input = $this->createRequest()->input()->get();

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertSame(3, $input->integer('page'));
        $this->assertTrue($input->boolean('filters.active'));
        $this->assertSame(1, $input->integer('missing', 1));
    }

    public function testPostReadsPostData(): void
    {
        service('superglobals')->setGet('remember', '0');
        service('superglobals')->setPost('remember', '1');
        service('superglobals')->setPost('tags', ['php', 'ci4']);

        $input = $this->createRequest()->input()->post();

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertTrue($input->boolean('remember'));
        $this->assertSame(['php', 'ci4'], $input->array('tags'));
    }

    public function testJsonReadsJsonBody(): void
    {
        $json = json_encode([
            'page'     => '4',
            'filters'  => ['active' => 'true'],
            'nullable' => null,
        ]);

        $input = $this->createRequest(new App(), $json)->input()->json();

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertSame(4, $input->integer('page'));
        $this->assertTrue($input->boolean('filters.active'));
        $this->assertTrue($input->has('nullable'));
    }

    public function testJsonReturnsEmptyInputForEmptyJsonBody(): void
    {
        $input = $this->createRequest(new App())->input()->json();

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertFalse($input->has('name'));
    }

    public function testJsonRejectsScalarJsonBody(): void
    {
        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage('The provided JSON format is not supported.');

        $this->createRequest(new App(), '"hello"')->input()->json();
    }

    public function testJsonKeepsInvalidJsonError(): void
    {
        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage('Failed to parse JSON string. Error: Syntax error');

        $this->createRequest(new App(), 'Invalid JSON string')->input()->json();
    }

    public function testRawReadsRawInputData(): void
    {
        $input = $this->createRequest(new App(), 'title=Hello&published=1')->input()->raw();

        $this->assertInstanceOf(InputData::class, $input);
        $this->assertSame('Hello', $input->string('title'));
        $this->assertTrue($input->boolean('published'));
    }
}
