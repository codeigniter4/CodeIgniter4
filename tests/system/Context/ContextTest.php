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

namespace CodeIgniter\Context;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ContextTest extends CIUnitTestCase
{
    public function testInitialState(): void
    {
        $context = service('context');
        $this->assertSame([], $context->getAll());
        $this->assertSame([], $context->getAllHidden());
    }

    public function testSetAndGetSingleValue(): void
    {
        $context = service('context');
        $context->set('user_id', 123);

        $this->assertSame(123, $context->get('user_id'));
        $this->assertNull($context->getHidden('user_id')); // Normal value should not be retrievable with getHidden()
    }

    public function testSetAndGetMultipleValues(): void
    {
        $context = service('context');
        $context->set([
            'user_id'  => 123,
            'username' => 'john_doe',
        ]);

        $this->assertSame(123, $context->get('user_id'));
        $this->assertSame('john_doe', $context->get('username'));
        $this->assertNull($context->getHidden('user_id'));
        $this->assertNull($context->getHidden('username'));
    }

    public function testSetAndGetSingleHiddenValue(): void
    {
        $context = service('context');
        $context->setHidden('api_key', 'secret');

        $this->assertSame('secret', $context->getHidden('api_key'));
        $this->assertNull($context->get('api_key')); // Hidden value should not be retrievable with get()
    }

    public function testSetAndGetMultipleHiddenValues(): void
    {
        $context = service('context');
        $context->setHidden([
            'api_key' => 'secret',
            'token'   => 'abc123',
        ]);

        $this->assertSame('secret', $context->getHidden('api_key'));
        $this->assertSame('abc123', $context->getHidden('token'));
        $this->assertNull($context->get('api_key'));
        $this->assertNull($context->get('token'));
    }

    public function testClear(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');

        $context->clear();

        $this->assertNull($context->get('user_id'));
        $this->assertNull($context->get('username'));
    }

    public function testClearDoesntAffectHidden(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret123');

        $context->clear();

        $this->assertNull($context->get('user_id'));
        $this->assertSame('secret123', $context->getHidden('api_key')); // Hidden value should still be retrievable after clear()
    }

    public function testClearHidden(): void
    {
        $context = service('context');
        $context->setHidden('api_key', 'abcdef');
        $context->setHidden('token', 'abc123');

        $context->clearHidden();

        $this->assertNull($context->getHidden('api_key'));
        $this->assertNull($context->getHidden('token'));
    }

    public function testClearHiddenDoesntAffectNormalValues(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret123');

        $context->clearHidden();

        $this->assertSame(123, $context->get('user_id')); // Normal value should still be retrievable after clearHidden()
        $this->assertNull($context->getHidden('api_key')); // Hidden value should be cleared
    }

    public function testClearAll(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret');

        $context->clearAll();

        $this->assertNull($context->get('user_id'));
        $this->assertNull($context->getHidden('api_key'));
    }

    public function testGetWithDefaultValue(): void
    {
        $context = service('context');

        $context->set('user_id', 123);

        $this->assertSame(123, $context->get('user_id', 'default')); // Existing key should return its value, not the default
        $this->assertSame('default', $context->get('non_existent_key', 'default'));
    }

    public function testGetOnlySingleKey(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $this->assertSame(['user_id' => 123], $context->getOnly('user_id'));
        $this->assertSame(['username' => 'john_doe'], $context->getOnly('username'));
        $this->assertSame([], $context->getOnly('non_existent_key'));
    }

    public function testGetOnlyMultipleKeys(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'user_id'  => 123,
            'username' => 'john_doe',
        ];
        $this->assertSame($expected, $context->getOnly(['user_id', 'username', 'non_existent_key'])); // non_existent_key should be ignored
    }

    public function testGetExceptSingleKey(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'username' => 'john_doe',
        ];
        $this->assertSame($expected, $context->getExcept('user_id')); // user_id should be excluded
    }

    public function testGetExceptMultipleKeys(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'username' => 'john_doe',
        ];
        $this->assertSame($expected, $context->getExcept(['user_id', 'non_existent_key'])); // user_id should be excluded, non_existent_key should be ignored
    }

    public function testGetAll(): void
    {
        $context = service('context');
        $context->set([
            'user_id'  => 123,
            'username' => 'john_doe',
        ]);

        $expected = [
            'user_id'  => 123,
            'username' => 'john_doe',
        ];

        $this->assertSame($expected, $context->getAll());
    }

    public function testGetHiddenWithDefaultValue(): void
    {
        $context = service('context');

        $context->setHidden('some_secret_token', '123456abcdefghij');

        $this->assertSame('123456abcdefghij', $context->getHidden('some_secret_token', 'foo')); // Existing key should return its value, not the default
        $this->assertSame('foo', $context->getHidden('api_key', 'foo'));
    }

    public function testGetOnlyHiddenSingleKey(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'some_secret_api_key_here');

        $this->assertSame(['api_key' => 'some_secret_api_key_here'], $context->getOnlyHidden('api_key'));
        $this->assertSame([], $context->getOnlyHidden('some_token'));
    }

    public function testGetOnlyHiddenMultipleKeys(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret');
        $context->setHidden('token', 'abc123');

        $expected = [
            'api_key' => 'secret',
            'token'   => 'abc123',
        ];
        $this->assertSame($expected, $context->getOnlyHidden(['api_key', 'token', 'non_existent_key'])); // non_existent_key should be ignored
    }

    public function testGetExceptHiddenSingleKey(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('some_sensitive_user_info', 'abcdefghij');
        $context->setHidden('api_key', 'some_secret_api_key_here');

        $expected = [
            'some_sensitive_user_info' => 'abcdefghij',
        ];

        $this->assertSame($expected, $context->getExceptHidden('api_key'));
    }

    public function testGetExceptHiddenMultipleKeys(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('token', 'abc123');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'token' => 'abc123',
        ];
        $this->assertSame($expected, $context->getExceptHidden(['api_key', 'non_existent_key'])); // token should be excluded, non_existent_key should be ignored
    }

    public function testGetAllHidden(): void
    {
        $context = service('context');
        $context->setHidden([
            'api_key' => 'secret',
            'token'   => 'abc123',
        ]);

        $expected = [
            'api_key' => 'secret',
            'token'   => 'abc123',
        ];

        $this->assertSame($expected, $context->getAllHidden());
    }

    public function testOverwriteExistingValue(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('user_id', 456); // Overwrite existing value

        $this->assertSame(456, $context->get('user_id'));
    }

    public function testOverwriteExistingHiddenValue(): void
    {
        $context = service('context');
        $context->setHidden('api_key', 'secret');
        $context->setHidden('api_key', 'new_secret'); // Overwrite existing hidden value

        $this->assertSame('new_secret', $context->getHidden('api_key'));
    }

    public function testSetHiddenDoesNotAffectNormalValues(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->setHidden('user_id', 'hidden_value');

        $this->assertSame(123, $context->get('user_id')); // Normal value should still be retrievable
        $this->assertSame('hidden_value', $context->getHidden('user_id')); // Hidden value should be retrievable with getHidden()
    }

    public function testHasKey(): void
    {
        $context = service('context');
        $this->assertFalse($context->has('user_id'));

        $context->set('user_id', 123);

        $this->assertTrue($context->has('user_id'));
    }

    public function testHasHiddenKey(): void
    {
        $context = service('context');
        $this->assertFalse($context->hasHidden('api_key'));

        $context->setHidden('api_key', 'secret');
        $this->assertTrue($context->hasHidden('api_key'));
    }

    public function testRemoveSingleValue(): void
    {
        $context = service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->remove('user_id');

        $this->assertNull($context->get('user_id'));
        $this->assertSame('john_doe', $context->get('username')); // Ensure other values are unaffected
    }

    public function testRemoveMultipleValues(): void
    {
        $context = service('context');
        $context->set([
            'user_id'  => 123,
            'username' => 'john_doe',
            'email'    => 'john@example.com',
        ]);

        $context->remove(['user_id', 'username']);

        $this->assertNull($context->get('user_id'));
        $this->assertNull($context->get('username'));
        $this->assertSame('john@example.com', $context->get('email')); // Ensure other values are unaffected
    }

    public function testRemoveHiddenValue(): void
    {
        $context = service('context');
        $context->setHidden('api_key', 'secret');
        $context->setHidden('token', 'abc123');

        $context->removeHidden('api_key');
        $this->assertNull($context->getHidden('api_key'));
        $this->assertSame('abc123', $context->getHidden('token')); // Ensure other hidden values are unaffected
    }

    public function testRemoveMultipleHiddenValues(): void
    {
        $context = service('context');
        $context->setHidden([
            'api_key'    => 'secret',
            'token'      => 'abc123',
            'session_id' => 'xyz789',
        ]);

        $context->removeHidden(['api_key', 'token']);

        $this->assertNull($context->getHidden('api_key'));
        $this->assertNull($context->getHidden('token'));
        $this->assertSame('xyz789', $context->getHidden('session_id')); // Ensure other hidden values are unaffected
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clear the context after each test to ensure isolation.
        $context = service('context');
        $context->clearAll();
    }
}
