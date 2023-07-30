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
use Tests\Support\Publishers\TestPublisher;

/**
 * @internal
 *
 * @group Others
 */
final class PublisherSupportTest extends CIUnitTestCase
{
    /**
     * A known, valid file
     */
    private string $file = SUPPORTPATH . 'Files/baker/banana.php';

    /**
     * A known, valid directory
     */
    private string $directory = SUPPORTPATH . 'Files/able/';

    /**
     * Initialize the helper, since some
     * tests call static methods before
     * the constructor would load it.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['filesystem']);
    }

    public function testDiscoverDefault(): void
    {
        $result = Publisher::discover();

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestPublisher::class, $result[0]);
    }

    public function testDiscoverNothing(): void
    {
        $result = Publisher::discover('Nothing');

        $this->assertSame([], $result);
    }

    public function testDiscoverStores(): void
    {
        $publisher = Publisher::discover()[0];
        $publisher->set([])->addFile($this->file);

        $result = Publisher::discover();
        $this->assertSame($publisher, $result[0]);
        $this->assertSame([$this->file], $result[0]->get());
    }

    public function testGetSource(): void
    {
        $publisher = new Publisher(ROOTPATH);

        $this->assertSame(ROOTPATH, $publisher->getSource());
    }

    public function testGetDestination(): void
    {
        $publisher = new Publisher(ROOTPATH, SUPPORTPATH);

        $this->assertSame(SUPPORTPATH, $publisher->getDestination());
    }

    public function testGetScratch(): void
    {
        $publisher = new Publisher();
        $this->assertNull($this->getPrivateProperty($publisher, 'scratch'));

        $scratch = $publisher->getScratch();

        $this->assertIsString($scratch);
        $this->assertDirectoryExists($scratch);
        $this->assertDirectoryIsWritable($scratch);
        $this->assertNotNull($this->getPrivateProperty($publisher, 'scratch'));

        // Directory and contents should be removed on __destruct()
        $file = $scratch . 'obvious_statement.txt';
        file_put_contents($file, 'Bananas are a most peculiar fruit');

        $publisher->__destruct();

        $this->assertFileDoesNotExist($file);
        $this->assertDirectoryDoesNotExist($scratch);
    }

    public function testGetErrors(): void
    {
        $publisher = new Publisher();
        $this->assertSame([], $publisher->getErrors());

        $expected = [
            $this->file => PublisherException::forCollision($this->file, $this->file),
        ];

        $this->setPrivateProperty($publisher, 'errors', $expected);

        $this->assertSame($expected, $publisher->getErrors());
    }

    public function testWipeDirectory(): void
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $this->assertDirectoryExists($directory);

        $method = $this->getPrivateMethodInvoker(Publisher::class, 'wipeDirectory');
        $method($directory);

        $this->assertDirectoryDoesNotExist($directory);
    }

    public function testWipeIgnoresFiles(): void
    {
        $method = $this->getPrivateMethodInvoker(Publisher::class, 'wipeDirectory');
        $method($this->file);

        $this->assertFileExists($this->file);
    }

    public function testWipe(): void
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $directory = realpath($directory) ?: $directory;
        $this->assertDirectoryExists($directory);
        config('Publisher')->restrictions[$directory] = ''; // Allow the directory

        $publisher = new Publisher($this->directory, $directory);
        $publisher->wipe();

        $this->assertDirectoryDoesNotExist($directory);
    }
}
