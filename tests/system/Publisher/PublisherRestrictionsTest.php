<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Publisher;

use CodeIgniter\Publisher\Exceptions\PublisherException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Publisher Restrictions Test
 *
 * Tests that the restrictions defined in the configuration
 * file properly prevent disallowed actions.
 *
 * @internal
 *
 * @group Others
 */
final class PublisherRestrictionsTest extends CIUnitTestCase
{
    /**
     * @see \Tests\Support\Config\Registrar::Publisher()
     */
    public function testRegistrarsNotAllowed(): void
    {
        $this->assertArrayNotHasKey(SUPPORTPATH, config('Publisher')->restrictions);
    }

    public function testImmutableRestrictions(): void
    {
        $publisher = new Publisher();

        // Try to "hack" the Publisher by adding our desired destination to the config
        config('Publisher')->restrictions[SUPPORTPATH] = '*';

        $restrictions = $this->getPrivateProperty($publisher, 'restrictions');

        $this->assertArrayNotHasKey(SUPPORTPATH, $restrictions);
    }

    /**
     * @dataProvider provideDefaultPublicRestrictions
     */
    public function testDefaultPublicRestrictions(string $path): void
    {
        $publisher = new Publisher(ROOTPATH, FCPATH);
        $pattern   = config('Publisher')->restrictions[FCPATH];

        // Use the scratch space to create a file
        $file = $publisher->getScratch() . $path;
        file_put_contents($file, 'To infinity and beyond!');

        $result = $publisher->addFile($file)->merge();
        $this->assertFalse($result);

        $errors = $publisher->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame([$file], array_keys($errors));

        $expected = lang('Publisher.fileNotAllowed', [$file, FCPATH, $pattern]);
        $this->assertSame($expected, $errors[$file]->getMessage());
    }

    public static function provideDefaultPublicRestrictions(): iterable
    {
        yield from [
            'php'  => ['index.php'],
            'exe'  => ['cat.exe'],
            'flat' => ['banana'],
        ];
    }

    /**
     * @dataProvider provideDestinations
     */
    public function testDestinations(string $destination, bool $allowed): void
    {
        config('Publisher')->restrictions = [
            APPPATH                   => '',
            FCPATH                    => '',
            SUPPORTPATH . 'Files'     => '',
            SUPPORTPATH . 'Files/../' => '',
        ];

        if (! $allowed) {
            $this->expectException(PublisherException::class);
            $this->expectExceptionMessage(lang('Publisher.destinationNotAllowed', [$destination]));
        }

        $publisher = new Publisher(null, $destination);
        $this->assertInstanceOf(Publisher::class, $publisher);
    }

    public static function provideDestinations(): iterable
    {
        return [
            'explicit' => [
                APPPATH,
                true,
            ],
            'subdirectory' => [
                APPPATH . 'Config',
                true,
            ],
            'relative' => [
                SUPPORTPATH . 'Files/able/../',
                true,
            ],
            'parent' => [
                SUPPORTPATH,
                false,
            ],
        ];
    }
}
