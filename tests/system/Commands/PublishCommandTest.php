<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use Tests\Support\Publishers\TestPublisher;

/**
 * @internal
 *
 * @group Others
 */
final class PublishCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        TestPublisher::setResult(true);
    }

    public function testDefault(): void
    {
        command('publish');

        $this->assertStringContainsString(lang('Publisher.publishSuccess', [
            TestPublisher::class,
            0,
            WRITEPATH,
        ]), $this->getStreamFilterBuffer());
    }

    public function testFailure(): void
    {
        TestPublisher::setResult(false);

        command('publish');

        $this->assertStringContainsString(lang('Publisher.publishFailure', [
            TestPublisher::class,
            WRITEPATH,
        ]), $this->getStreamFilterBuffer());
    }
}
