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
        $context = single_service('context');
        $this->assertSame([], $context->getAll());
        $this->assertSame([], $context->getAllHidden());
    }

    public function testSetAndGetSingleValue(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);

        $this->assertSame(123, $context->get('user_id'));
        $this->assertNull($context->getHidden('user_id')); // Normal value should not be retrievable with getHidden()
    }

    public function testSetAndGetSingleValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.id', 123);

        $this->assertSame(123, $context->get('user.id'));
        $this->assertNull($context->getHidden('user.id')); // Normal value should not be retrievable with getHidden()
    }

    public function testSetAndGetMultipleValues(): void
    {
        $context = single_service('context');
        $context->set([
            'user_id'  => 123,
            'username' => 'john_doe',
        ]);

        $this->assertSame(123, $context->get('user_id'));
        $this->assertSame('john_doe', $context->get('username'));
        $this->assertNull($context->getHidden('user_id'));
        $this->assertNull($context->getHidden('username'));
    }

    public function testSetAndGetMultipleValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set([
            'user.profile.name'  => 'John Doe',
            'user.profile.email' => 'john@example.com',
        ]);

        $this->assertSame('John Doe', $context->get('user.profile.name'));
        $this->assertSame('john@example.com', $context->get('user.profile.email'));
        $this->assertNull($context->getHidden('user.profile.name')); // Normal value should not be retrievable with getHidden()
    }

    public function testSetAndGetSingleHiddenValue(): void
    {
        $context = single_service('context');
        $context->setHidden('api_key', 'secret');

        $this->assertSame('secret', $context->getHidden('api_key'));
        $this->assertNull($context->get('api_key')); // Hidden value should not be retrievable with get()
    }

    public function testSetAndGetSingleHiddenValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden('api.credentials.key', 'secret');

        $this->assertSame('secret', $context->getHidden('api.credentials.key'));
        $this->assertNull($context->get('api.credentials.key')); // Hidden value should not be retrievable with get()
    }

    public function testSetAndGetMultipleHiddenValues(): void
    {
        $context = single_service('context');
        $context->setHidden([
            'api_key' => 'secret',
            'token'   => 'abc123',
        ]);

        $this->assertSame('secret', $context->getHidden('api_key'));
        $this->assertSame('abc123', $context->getHidden('token'));
        $this->assertNull($context->get('api_key'));
        $this->assertNull($context->get('token'));
    }

    public function testSetAndGetMultipleHiddenValuesWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden([
            'api.credentials.key'   => 'secret',
            'api.credentials.token' => 'abc123',
        ]);

        $this->assertSame('secret', $context->getHidden('api.credentials.key'));
        $this->assertSame('abc123', $context->getHidden('api.credentials.token'));
        $this->assertNull($context->get('api.credentials.key'));
        $this->assertNull($context->get('api.credentials.token'));
    }

    public function testClear(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');

        $context->clear();

        $this->assertNull($context->get('user_id'));
        $this->assertNull($context->get('username'));
    }

    public function testClearWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->set('user.profile.email', 'john@example.com');

        $context->clear();

        $this->assertNull($context->get('user.profile.name'));
        $this->assertNull($context->get('user.profile.email'));
    }

    public function testClearDoesntAffectHidden(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret123');

        $context->clear();

        $this->assertNull($context->get('user_id'));
        $this->assertSame('secret123', $context->getHidden('api_key')); // Hidden value should still be retrievable after clear()
    }

    public function testClearWithDotNotationDoesntAffectHidden(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->setHidden('api.credentials.key', 'secret123');

        $context->clear();

        $this->assertNull($context->get('user.profile.name'));
        $this->assertSame('secret123', $context->getHidden('api.credentials.key')); // Hidden value should still be retrievable after clear()
    }

    public function testClearHidden(): void
    {
        $context = single_service('context');
        $context->setHidden('api_key', 'abcdef');
        $context->setHidden('token', 'abc123');

        $context->clearHidden();

        $this->assertNull($context->getHidden('api_key'));
        $this->assertNull($context->getHidden('token'));
    }

    public function testClearHiddenWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden('api.credentials.key', 'abcdef');
        $context->setHidden('api.credentials.token', 'abc123');

        $context->clearHidden();

        $this->assertNull($context->getHidden('api.credentials.key'));
        $this->assertNull($context->getHidden('api.credentials.token'));
    }

    public function testClearHiddenDoesntAffectNormalValues(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret123');

        $context->clearHidden();

        $this->assertSame(123, $context->get('user_id')); // Normal value should still be retrievable after clearHidden()
        $this->assertNull($context->getHidden('api_key')); // Hidden value should be cleared
    }

    public function testClearHiddenWithDotNotationDoesntAffectNormalValues(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->setHidden('api.credentials.key', 'secret123');

        $context->clearHidden();

        $this->assertSame('John Doe', $context->get('user.profile.name')); // Normal value should still be retrievable after clearHidden()
        $this->assertNull($context->getHidden('api.credentials.key')); // Hidden value should be cleared
    }

    public function testClearAll(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret');

        $context->clearAll();

        $this->assertNull($context->get('user_id'));
        $this->assertNull($context->getHidden('api_key'));
    }

    public function testClearAllWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->setHidden('api.credentials.key', 'secret');

        $context->clearAll();

        $this->assertNull($context->get('user.profile.name'));
        $this->assertNull($context->getHidden('api.credentials.key'));
    }

    public function testGetWithDefaultValue(): void
    {
        $context = single_service('context');

        $context->set('user_id', 123);

        $this->assertSame(123, $context->get('user_id', 'default')); // Existing key should return its value, not the default
        $this->assertSame('default', $context->get('non_existent_key', 'default'));
    }

    public function testGetWithDotNotationAndDefaultValue(): void
    {
        $context = single_service('context');

        $context->set('user.profile.name', 'John Doe');

        $this->assertSame('John Doe', $context->get('user.profile.name', 'default')); // Existing key should return its value, not the default
        $this->assertSame('default', $context->get('user.profile.non_existent_key', 'default'));
    }

    public function testGetOnlySingleKey(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $this->assertSame(['user_id' => 123], $context->getOnly('user_id'));
        $this->assertSame(['username' => 'john_doe'], $context->getOnly('username'));
        $this->assertSame([], $context->getOnly('non_existent_key'));
    }

    public function testGetOnlySingleKeyWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->setHidden('api.credentials.key', 'secret');

        $this->assertSame([
            'user' => [
                'profile' => [
                    'name' => 'John Doe',
                ],
            ],
        ], $context->getOnly('user.profile.name'));
        $this->assertSame([], $context->getOnly('user.profile.non_existent_key'));
    }

    public function testGetOnlyMultipleKeys(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'user_id'  => 123,
            'username' => 'john_doe',
        ];
        $this->assertSame($expected, $context->getOnly(['user_id', 'username', 'non_existent_key'])); // non_existent_key should be ignored
    }

    public function testGetOnlyMultipleKeysWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->set('user.profile.email', 'john.doe@example.com');
        $context->setHidden('api.credentials.key', 'secret');

        $expected = [
            'user' => [
                'profile' => [
                    'name'  => 'John Doe',
                    'email' => 'john.doe@example.com',
                ],
            ],
        ];

        $this->assertSame($expected, $context->getOnly(['user.profile.name', 'user.profile.email', 'user.profile.non_existent_key'])); // non_existent_key should be ignored
    }

    public function testGetExceptSingleKey(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'username' => 'john_doe',
        ];
        $this->assertSame($expected, $context->getExcept('user_id')); // user_id should be excluded
    }

    public function testGetExceptSingleKeyWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->set('user.profile.email', 'john.doe@example.com');
        $context->setHidden('api.credentials.key', 'secret');

        $expected = [
            'user' => [
                'profile' => [
                    'email' => 'john.doe@example.com',
                ],
            ],
        ];
        $this->assertSame($expected, $context->getExcept('user.profile.name')); // user.profile.name should be excluded
    }

    public function testGetExceptMultipleKeys(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'username' => 'john_doe',
        ];
        $this->assertSame($expected, $context->getExcept(['user_id', 'non_existent_key'])); // user_id should be excluded, non_existent_key should be ignored
    }

    public function testGetExceptMultipleKeysWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->set('user.profile.email', 'john.doe@example.com');
        $context->setHidden('api.credentials.key', 'secret');

        $expected = [
            'user' => [
                'profile' => [
                    'email' => 'john.doe@example.com',
                ],
            ],
        ];
        $this->assertSame($expected, $context->getExcept(['user.profile.name', 'non_existent_key'])); // user.profile.name should be excluded, non_existent_key should be ignored
    }

    public function testGetAll(): void
    {
        $context = single_service('context');
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

    public function testGetAllWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set([
            'user.profile.name' => 'John Doe',
            'request.corr_id'   => 'abc123',
        ]);

        $expected = [
            'user' => [
                'profile' => [
                    'name' => 'John Doe',
                ],
            ],
            'request' => [
                'corr_id' => 'abc123',
            ],
        ];

        $this->assertSame($expected, $context->getAll());
    }

    public function testGetHiddenWithDefaultValue(): void
    {
        $context = single_service('context');

        $context->setHidden('some_secret_token', '123456abcdefghij');

        $this->assertSame('123456abcdefghij', $context->getHidden('some_secret_token', 'foo')); // Existing key should return its value, not the default
        $this->assertSame('foo', $context->getHidden('api_key', 'foo'));
    }

    public function testGetHiddenWithDotNotationAndDefaultValue(): void
    {
        $context = single_service('context');

        $context->setHidden('api.credentials.key', 'secret12345');

        $this->assertSame('secret12345', $context->getHidden('api.credentials.key', 'default')); // Existing key should return its value, not the default
        $this->assertSame('default', $context->getHidden('api.credentials.non_existent_key', 'default'));
    }

    public function testGetOnlyHiddenSingleKey(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'some_secret_api_key_here');

        $this->assertSame(['api_key' => 'some_secret_api_key_here'], $context->getOnlyHidden('api_key'));
        $this->assertSame([], $context->getOnlyHidden('some_token'));
    }

    public function testGetOnlyHiddenSingleKeyWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api.credentials.key', 'some_secret_api_key_here');

        $this->assertSame(['api' => ['credentials' => ['key' => 'some_secret_api_key_here']]], $context->getOnlyHidden('api.credentials.key'));
        $this->assertSame([], $context->getOnlyHidden('api.credentials.non_existent_key'));
    }

    public function testGetOnlyHiddenMultipleKeys(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api_key', 'secret');
        $context->setHidden('token', 'abc123');

        $expected = [
            'api_key' => 'secret',
            'token'   => 'abc123',
        ];
        $this->assertSame($expected, $context->getOnlyHidden(['api_key', 'token', 'non_existent_key'])); // non_existent_key should be ignored
    }

    public function testGetOnlyHiddenMultipleKeysWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api.credentials.key', 'secret');
        $context->setHidden('api.credentials.token', 'abc123');

        $expected = [
            'api' => [
                'credentials' => [
                    'key'   => 'secret',
                    'token' => 'abc123',
                ],
            ],
        ];
        $this->assertSame($expected, $context->getOnlyHidden(['api.credentials.key', 'api.credentials.token', 'api.credentials.non_existent_key'])); // non_existent_key should be ignored
    }

    public function testGetExceptHiddenSingleKey(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('some_sensitive_user_info', 'abcdefghij');
        $context->setHidden('api_key', 'some_secret_api_key_here');

        $expected = [
            'some_sensitive_user_info' => 'abcdefghij',
        ];

        $this->assertSame($expected, $context->getExceptHidden('api_key'));
    }

    public function testGetExceptHiddenSingleKeyWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api.credentials.key', 'secret');
        $context->setHidden('api.credentials.token', 'abc123');

        $expected = [
            'api' => [
                'credentials' => [
                    'key' => 'secret',
                ],
            ],
        ];

        $this->assertSame($expected, $context->getExceptHidden('api.credentials.token'));
    }

    public function testGetExceptHiddenMultipleKeys(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('token', 'abc123');
        $context->setHidden('api_key', 'secret');

        $expected = [
            'token' => 'abc123',
        ];
        $this->assertSame($expected, $context->getExceptHidden(['api_key', 'non_existent_key'])); // token should be excluded, non_existent_key should be ignored
    }

    public function testGetExceptHiddenMultipleKeysWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('api.credentials.key', 'secret');
        $context->setHidden('api.credentials.token', 'abc123');
        $context->setHidden('api.credentials.session_id', 'xyz789');

        $expected = [
            'api' => [
                'credentials' => [
                    'key'        => 'secret',
                    'session_id' => 'xyz789',
                ],
            ],
        ];
        $this->assertSame($expected, $context->getExceptHidden(['api.credentials.token', 'non_existent_key'])); // api.credentials.token should be excluded, non_existent_key should be ignored
    }

    public function testGetAllHidden(): void
    {
        $context = single_service('context');
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

    public function testGetAllHiddenWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden([
            'api.credentials.key'   => 'secret',
            'api.credentials.token' => 'abc123',
        ]);

        $expected = [
            'api' => [
                'credentials' => [
                    'key'   => 'secret',
                    'token' => 'abc123',
                ],
            ],
        ];

        $this->assertSame($expected, $context->getAllHidden());
    }

    public function testOverwriteExistingValue(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('user_id', 456); // Overwrite existing value

        $this->assertSame(456, $context->get('user_id'));
    }

    public function testOverwriteExistingValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->set('user.profile.name', 'Something Different'); // Overwrite existing value

        $this->assertSame('Something Different', $context->get('user.profile.name'));
    }

    public function testOverwriteExistingHiddenValue(): void
    {
        $context = single_service('context');
        $context->setHidden('api_key', 'secret');
        $context->setHidden('api_key', 'new_secret'); // Overwrite existing hidden value

        $this->assertSame('new_secret', $context->getHidden('api_key'));
    }

    public function testOverwriteExistingHiddenValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden('api.credentials.key', 'secret');
        $context->setHidden('api.credentials.key', 'new_secret'); // Overwrite existing hidden value

        $this->assertSame('new_secret', $context->getHidden('api.credentials.key'));
    }

    public function testSetHiddenDoesNotAffectNormalValues(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->setHidden('user_id', 'hidden_value');

        $this->assertSame(123, $context->get('user_id')); // Normal value should still be retrievable
        $this->assertSame('hidden_value', $context->getHidden('user_id')); // Hidden value should be retrievable with getHidden()
    }

    public function testSetHiddenWithDotNotationDoesNotAffectNormalValues(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->setHidden('user.profile.name', 'Hidden Name');

        $this->assertSame('John Doe', $context->get('user.profile.name')); // Normal value should still be retrievable
        $this->assertSame('Hidden Name', $context->getHidden('user.profile.name')); // Hidden value should be retrievable with getHidden()
    }

    public function testHasKey(): void
    {
        $context = single_service('context');
        $this->assertFalse($context->has('user_id'));

        $context->set('user_id', 123);

        $this->assertTrue($context->has('user_id'));
    }

    public function testHasKeyWithDotNotation(): void
    {
        $context = single_service('context');
        $this->assertFalse($context->has('user.profile.name'));

        $context->set('user.profile.name', 'John Doe');

        $this->assertTrue($context->has('user.profile.name'));
    }

    public function testHasHiddenKey(): void
    {
        $context = single_service('context');
        $this->assertFalse($context->hasHidden('api_key'));

        $context->setHidden('api_key', 'secret');
        $this->assertTrue($context->hasHidden('api_key'));
    }

    public function testHasHiddenKeyWithDotNotation(): void
    {
        $context = single_service('context');
        $this->assertFalse($context->hasHidden('api.credentials.key'));

        $context->setHidden('api.credentials.key', 'secret');
        $this->assertTrue($context->hasHidden('api.credentials.key'));
    }

    public function testRemoveSingleValue(): void
    {
        $context = single_service('context');
        $context->set('user_id', 123);
        $context->set('username', 'john_doe');
        $context->remove('user_id');

        $this->assertNull($context->get('user_id'));
        $this->assertSame('john_doe', $context->get('username')); // Ensure other values are unaffected
    }

    public function testRemoveSingleValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set('user.profile.name', 'John Doe');
        $context->set('user.profile.email', 'john@example.com');
        $context->remove('user.profile.name');

        $this->assertNull($context->get('user.profile.name'));
        $this->assertSame('john@example.com', $context->get('user.profile.email')); // Ensure other values are unaffected
    }

    public function testRemoveMultipleValues(): void
    {
        $context = single_service('context');
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

    public function testRemoveMultipleValuesWithDotNotation(): void
    {
        $context = single_service('context');
        $context->set([
            'user.id'         => 123,
            'request.corr_id' => '12345',
            'user.email'      => 'john@example.com',
        ]);

        $context->remove(['user.id', 'request.corr_id']);

        $this->assertNull($context->get('user.id'));
        $this->assertNull($context->get('request.corr_id'));
        $this->assertSame('john@example.com', $context->get('user.email'));
    }

    public function testRemoveHiddenValue(): void
    {
        $context = single_service('context');
        $context->setHidden('api_key', 'secret');
        $context->setHidden('token', 'abc123');

        $context->removeHidden('api_key');
        $this->assertNull($context->getHidden('api_key'));
        $this->assertSame('abc123', $context->getHidden('token')); // Ensure other hidden values are unaffected
    }

    public function testRemoveHiddenValueWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden('credentials.api_key', 'secret');
        $context->setHidden('credentials.token', 'abc123');

        $context->removeHidden('credentials.api_key');
        $this->assertNull($context->getHidden('credentials.api_key'));
        $this->assertSame('abc123', $context->getHidden('credentials.token')); // Ensure other hidden values are unaffected
    }

    public function testRemoveMultipleHiddenValues(): void
    {
        $context = single_service('context');
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

    public function testRemoveMultipleHiddenValuesWithDotNotation(): void
    {
        $context = single_service('context');
        $context->setHidden([
            'credentials.api_key' => 'secret',
            'credentials.token'   => 'abc123',
            'session_id'          => 'xyz789',
        ]);

        $context->removeHidden(['credentials.api_key', 'credentials.token']);

        $this->assertNull($context->getHidden('credentials.api_key'));
        $this->assertNull($context->getHidden('credentials.token'));
        $this->assertSame('xyz789', $context->getHidden('session_id')); // Ensure other hidden values are unaffected
    }

    public function testPrintRDoesNotExposeHiddenValues(): void
    {
        $context = new Context();
        $context->set('user_id', 123);
        $context->setHidden('credentials.api_key', 'secret');

        $output = print_r($context, true);

        $this->assertStringContainsString('user_id', $output);
        $this->assertStringNotContainsString('secret', $output);
        $this->assertStringContainsString('SensitiveParameterValue', $output);
    }

    public function testCloneDoesNotCopyHiddenValues(): void
    {
        $context = new Context();
        $context->set('user_id', 123);
        $context->setHidden('credentials.api_key', 'secret');

        $clonedContext = clone $context;

        $this->assertSame(123, $clonedContext->get('user_id')); // Normal value should be copied
        $this->assertNull($clonedContext->getHidden('credentials.api_key')); // Hidden value should not be copied
    }

    public function testSerializationDoesNotIncludeHiddenValues(): void
    {
        $context = new Context();
        $context->set('user_id', 123);
        $context->setHidden('credentials.api_key', 'secret');

        $serialized = serialize($context);

        $this->assertStringContainsString('user_id', $serialized);
        $this->assertStringNotContainsString('secret', $serialized);

        $unserializedContext = unserialize($serialized);

        $this->assertSame(123, $unserializedContext->get('user_id')); // Normal value should be preserved
        $this->assertNull($unserializedContext->getHidden('credentials.api_key')); // Hidden value should not be preserved
    }
}
