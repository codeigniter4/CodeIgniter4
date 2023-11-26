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

use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @internal
 *
 * @group Others
 */
final class PublisherOutputTest extends CIUnitTestCase
{
    /**
     * Files to seed to VFS
     */
    private array $structure;

    /**
     * Virtual destination
     */
    private vfsStreamDirectory $root;

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->structure = [
            'able' => [
                'apple.php' => 'Once upon a midnight dreary',
                'bazam'     => 'While I pondered weak and weary',
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door',
            ],
            'AnEmptyFolder' => [],
            'simpleFile'    => 'A tap-tap-tapping upon my door',
            '.hidden'       => 'There is no spoon',
        ];

        $this->root = vfsStream::setup('root', null, $this->structure);

        // Add root to the list of allowed destinations
        config('Publisher')->restrictions[$this->root->url()] = '*';
    }

    public function testCopy(): void
    {
        $publisher = new Publisher($this->directory, $this->root->url());
        $publisher->addFile($this->file);

        $this->assertFileDoesNotExist($this->root->url() . '/banana.php');

        $result = $publisher->copy(false);

        $this->assertTrue($result);
        $this->assertFileExists($this->root->url() . '/banana.php');
    }

    public function testCopyReplace(): void
    {
        $file      = $this->directory . 'apple.php';
        $publisher = new Publisher($this->directory, $this->root->url() . '/able');
        $publisher->addFile($file);

        $this->assertFileExists($this->root->url() . '/able/apple.php');
        $this->assertFalse(same_file($file, $this->root->url() . '/able/apple.php'));

        $result = $publisher->copy(true);

        $this->assertTrue($result);
        $this->assertTrue(same_file($file, $this->root->url() . '/able/apple.php'));
    }

    public function testCopyIgnoresSame(): void
    {
        $publisher = new Publisher($this->directory, $this->root->url());
        $publisher->addFile($this->file);

        copy($this->file, $this->root->url() . '/banana.php');

        $result = $publisher->copy(false);
        $this->assertTrue($result);

        $result = $publisher->copy(true);
        $this->assertTrue($result);
        $this->assertSame([$this->root->url() . '/banana.php'], $publisher->getPublished());
    }

    public function testCopyIgnoresCollision(): void
    {
        $publisher = new Publisher($this->directory, $this->root->url());

        mkdir($this->root->url() . '/banana.php');

        $result = $publisher->addFile($this->file)->copy(false);

        $this->assertTrue($result);
        $this->assertSame([], $publisher->getErrors());
        $this->assertSame([$this->root->url() . '/banana.php'], $publisher->getPublished());
    }

    public function testCopyCollides(): void
    {
        $publisher = new Publisher($this->directory, $this->root->url());
        $expected  = lang('Publisher.collision', ['dir', $this->file, $this->root->url() . '/banana.php']);

        mkdir($this->root->url() . '/banana.php');

        $result = $publisher->addFile($this->file)->copy(true);
        $errors = $publisher->getErrors();

        $this->assertFalse($result);
        $this->assertCount(1, $errors);
        $this->assertSame([$this->file], array_keys($errors));
        $this->assertSame([], $publisher->getPublished());
        $this->assertSame($expected, $errors[$this->file]->getMessage());
    }

    public function testMerge(): void
    {
        $publisher = new Publisher(SUPPORTPATH . 'Files', $this->root->url());
        $expected  = [
            $this->root->url() . '/able/apple.php',
            $this->root->url() . '/able/fig_3.php',
            $this->root->url() . '/able/prune_ripe.php',
            $this->root->url() . '/baker/banana.php',
        ];

        $this->assertFileDoesNotExist($this->root->url() . '/able/fig_3.php');
        $this->assertDirectoryDoesNotExist($this->root->url() . '/baker');

        $result = $publisher->addPath('/')->merge(false);

        $this->assertTrue($result);
        $this->assertFileExists($this->root->url() . '/able/fig_3.php');
        $this->assertDirectoryExists($this->root->url() . '/baker');
        $this->assertSame($expected, $publisher->getPublished());
    }

    public function testMergeReplace(): void
    {
        $this->assertFalse(same_file($this->directory . 'apple.php', $this->root->url() . '/able/apple.php'));
        $publisher = new Publisher(SUPPORTPATH . 'Files', $this->root->url());
        $expected  = [
            $this->root->url() . '/able/apple.php',
            $this->root->url() . '/able/fig_3.php',
            $this->root->url() . '/able/prune_ripe.php',
            $this->root->url() . '/baker/banana.php',
        ];

        $result = $publisher->addPath('/')->merge(true);

        $this->assertTrue($result);
        $this->assertTrue(same_file($this->directory . 'apple.php', $this->root->url() . '/able/apple.php'));
        $this->assertSame($expected, $publisher->getPublished());
    }

    public function testMergeCollides(): void
    {
        $publisher = new Publisher(SUPPORTPATH . 'Files', $this->root->url());
        $expected  = lang('Publisher.collision', ['dir', $this->directory . 'fig_3.php', $this->root->url() . '/able/fig_3.php']);
        $published = [
            $this->root->url() . '/able/apple.php',
            $this->root->url() . '/able/prune_ripe.php',
            $this->root->url() . '/baker/banana.php',
        ];

        mkdir($this->root->url() . '/able/fig_3.php');

        $result = $publisher->addPath('/')->merge(true);
        $errors = $publisher->getErrors();

        $this->assertFalse($result);
        $this->assertCount(1, $errors);
        $this->assertSame([$this->directory . 'fig_3.php'], array_keys($errors));
        $this->assertSame($published, $publisher->getPublished());
        $this->assertSame($expected, $errors[$this->directory . 'fig_3.php']->getMessage());
    }

    public function testPublish(): void
    {
        $publisher = new Publisher(SUPPORTPATH . 'Files', $this->root->url());

        $result = $publisher->publish();

        $this->assertTrue($result);
        $this->assertFileExists($this->root->url() . '/able/fig_3.php');
        $this->assertDirectoryExists($this->root->url() . '/baker');
        $this->assertTrue(same_file($this->directory . 'apple.php', $this->root->url() . '/able/apple.php'));
    }
}
