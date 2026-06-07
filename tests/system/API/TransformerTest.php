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

namespace CodeIgniter\API;

use CodeIgniter\Entity\Entity;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\API\ChildTransformer;
use Tests\Support\API\ParentTransformer;

/**
 * @internal
 */
#[Group('Others')]
final class TransformerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Services::resetSingle('request');
        Services::superglobals()->setGetArray([]);
    }

    protected function tearDown(): void
    {
        Services::superglobals()->setGetArray([]);
        Services::resetSingle('request');

        parent::tearDown();
    }

    private function createMockRequest(string $query = ''): IncomingRequest
    {
        $config    = new MockAppConfig();
        $uri       = new SiteURI($config, 'http://example.com/test' . ($query !== '' ? '?' . $query : ''));
        $userAgent = new UserAgent();

        $request = $this->getMockBuilder(IncomingRequest::class)
            ->setConstructorArgs([$config, $uri, null, $userAgent])
            ->onlyMethods(['isCLI'])
            ->getMock();
        $request->method('isCLI')->willReturn(false);

        // Parse query string and set GET globals
        if ($query !== '') {
            parse_str($query, $get);
            $request->setGlobal('get', $get);
        } else {
            $request->setGlobal('get', []);
        }

        return $request;
    }

    public function testConstructorWithNoRequest(): void
    {
        $transformer = new class () extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => 1, 'name' => 'Test'];
            }
        };

        $result = $transformer->transform();

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    public function testConstructorWithRequest(): void
    {
        $request = $this->createMockRequest();

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => 1, 'name' => 'Test'];
            }
        };

        $result = $transformer->transform();

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    public function testTransformWithNull(): void
    {
        $request = $this->createMockRequest();

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => 1, 'name' => 'Test'];
            }
        };

        $result = $transformer->transform();

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    public function testTransformWithEntity(): void
    {
        $request = $this->createMockRequest();
        $entity  = new class () extends Entity {
            protected $attributes = [
                'id'   => 1,
                'name' => 'Test Entity',
            ];
        };

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($entity);

        $this->assertSame(['id' => 1, 'name' => 'Test Entity'], $result);
    }

    public function testTransformWithArray(): void
    {
        $request = $this->createMockRequest();
        $data    = ['id' => 1, 'name' => 'Test Array'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test Array'], $result);
    }

    public function testTransformWithObject(): void
    {
        $request      = $this->createMockRequest();
        $object       = new stdClass();
        $object->id   = 1;
        $object->name = 'Test Object';

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($object);

        $this->assertSame(['id' => 1, 'name' => 'Test Object'], $result);
    }

    public function testTransformMany(): void
    {
        $request = $this->createMockRequest();
        $data    = [
            ['id' => 1, 'name' => 'First'],
            ['id' => 2, 'name' => 'Second'],
            ['id' => 3, 'name' => 'Third'],
        ];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transformMany($data);

        $this->assertCount(3, $result);
        $this->assertSame(['id' => 1, 'name' => 'First'], $result[0]);
        $this->assertSame(['id' => 2, 'name' => 'Second'], $result[1]);
        $this->assertSame(['id' => 3, 'name' => 'Third'], $result[2]);
    }

    public function testTransformManyWithEmptyArray(): void
    {
        $request = $this->createMockRequest();

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource ?? [];
            }
        };

        $result = $transformer->transformMany([]);

        $this->assertSame([], $result);
    }

    public function testLimitFieldsWithNoFieldsParam(): void
    {
        $request = $this->createMockRequest();
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'], $result);
    }

    public function testLimitFieldsWithFieldsParam(): void
    {
        $request = $this->createMockRequest('fields=id,name');
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    public function testLimitFieldsWithSingleField(): void
    {
        $request = $this->createMockRequest('fields=name');
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['name' => 'Test'], $result);
    }

    public function testLimitFieldsWithSpaces(): void
    {
        $request = $this->createMockRequest('fields=id, name, email');
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com', 'bio' => 'Bio'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'], $result);
    }

    public function testLimitFieldsWithAllowedFieldsValidation(): void
    {
        $request = $this->createMockRequest('fields=id,name');
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            protected function getAllowedFields(): array
            {
                return ['id', 'name', 'email'];
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
    }

    public function testLimitFieldsThrowsExceptionForInvalidField(): void
    {
        $this->expectException(ApiException::class);

        $request = $this->createMockRequest('fields=id,password');
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            protected function getAllowedFields(): array
            {
                return ['id', 'name', 'email'];
            }
        };

        $transformer->transform($data);
    }

    public function testInsertIncludesWithNoIncludeParam(): void
    {
        $request = $this->createMockRequest();
        $data    = ['id' => 1, 'name' => 'Test'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
        $this->assertArrayNotHasKey('posts', $result);
    }

    public function testInsertIncludesWithIncludeParam(): void
    {
        $request = $this->createMockRequest('include=posts');
        $data    = ['id' => 1, 'name' => 'Test'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame([
            'id'    => 1,
            'name'  => 'Test',
            'posts' => [['id' => 1, 'title' => 'Post 1']],
        ], $result);
    }

    public function testInsertIncludesWithMultipleIncludes(): void
    {
        $request = $this->createMockRequest('include=posts,comments');
        $data    = ['id' => 1, 'name' => 'Test'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }

            /**
             * @return list<array{id: int, text: string}>
             */
            protected function includeComments(): array
            {
                return [['id' => 1, 'text' => 'Comment 1']];
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame([
            'id'       => 1,
            'name'     => 'Test',
            'posts'    => [['id' => 1, 'title' => 'Post 1']],
            'comments' => [['id' => 1, 'text' => 'Comment 1']],
        ], $result);
    }

    public function testInsertIncludesThrowsExceptionForNonExistentMethod(): void
    {
        $request = $this->createMockRequest('include=posts,nonexistent');
        $data    = ['id' => 1, 'name' => 'Test'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(lang('Api.missingInclude', ['nonexistent']));

        $transformer->transform($data);
    }

    public function testInsertIncludesWithEmptyAllowedIncludes(): void
    {
        $request = $this->createMockRequest('include=posts');
        $data    = ['id' => 1, 'name' => 'Test'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            protected function getAllowedIncludes(): array
            {
                return [];
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame(['id' => 1, 'name' => 'Test'], $result);
        $this->assertArrayNotHasKey('posts', $result);
    }

    public function testCombinedFieldsAndIncludes(): void
    {
        $request = $this->createMockRequest('fields=id,name&include=posts');
        $data    = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $result = $transformer->transform($data);

        $this->assertSame([
            'id'    => 1,
            'name'  => 'Test',
            'posts' => [['id' => 1, 'title' => 'Post 1']],
        ], $result);
        $this->assertArrayNotHasKey('email', $result);
    }

    public function testTransformManyWithFieldsFilter(): void
    {
        $request = $this->createMockRequest('fields=id,name');
        $data    = [
            ['id' => 1, 'name' => 'First', 'email' => 'first@example.com'],
            ['id' => 2, 'name' => 'Second', 'email' => 'second@example.com'],
        ];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $transformer->transformMany($data);

        $this->assertCount(2, $result);
        $this->assertSame(['id' => 1, 'name' => 'First'], $result[0]);
        $this->assertSame(['id' => 2, 'name' => 'Second'], $result[1]);
    }

    public function testTransformManyWithIncludes(): void
    {
        $request = $this->createMockRequest('include=posts');
        $data    = [
            ['id' => 1, 'name' => 'First'],
            ['id' => 2, 'name' => 'Second'],
        ];

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $result = $transformer->transformMany($data);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('posts', $result[0]);
        $this->assertArrayHasKey('posts', $result[1]);
    }

    public function testTransformThrowsExceptionForInvalidInclude(): void
    {
        $request = $this->createMockRequest('include=nonexistent');

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => $resource['id'], 'name' => $resource['name']];
            }
        };

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(lang('Api.missingInclude', ['nonexistent']));

        $data = ['id' => 1, 'name' => 'Test'];
        $transformer->transform($data);
    }

    public function testTransformThrowsExceptionForMissingIncludeMethod(): void
    {
        $request = $this->createMockRequest('include=invalid');

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => $resource['id'], 'name' => $resource['name']];
            }
        };

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(lang('Api.missingInclude', ['invalid']));

        $data = ['id' => 1, 'name' => 'Test'];
        $transformer->transform($data);
    }

    public function testTransformWithMultipleIncludesValidatesAll(): void
    {
        $request = $this->createMockRequest('include=posts,invalid');

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => $resource['id'], 'name' => $resource['name']];
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(lang('Api.missingInclude', ['invalid']));

        $data = ['id' => 1, 'name' => 'Test'];
        $transformer->transform($data);
    }

    public function testTransformWithValidIncludeDoesNotThrowException(): void
    {
        $request = $this->createMockRequest('include=posts');

        $transformer = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return ['id' => $resource['id'], 'name' => $resource['name']];
            }

            /**
             * @return list<array{id: int, title: string}>
             */
            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }
        };

        $data   = ['id' => 1, 'name' => 'Test'];
        $result = $transformer->transform($data);

        $this->assertArrayHasKey('posts', $result);
        $this->assertSame([['id' => 1, 'title' => 'Post 1']], $result['posts']);
    }

    public function testNestedTransformerDoesNotInheritIncludeState(): void
    {
        // The child transformer has no includeChildren() method. If the root
        // request's `include=children` leaked into it, transforming the child
        // would raise an ApiException for the missing include method.
        $request = $this->createMockRequest('include=children');
        Services::injectMock('request', $request);

        $transformer = new ParentTransformer($request);

        $result = $transformer->transform(['id' => 1]);

        $this->assertSame([
            'parent_id' => 1,
            'children'  => ['child_id' => 99, 'status' => 'transformed'],
        ], $result);
    }

    public function testNestedTransformerDoesNotInheritFieldFilter(): void
    {
        // `fields=parent_id` applies to the root only. If it leaked into the
        // child, array_intersect_key would strip every child field, leaving [].
        $request = $this->createMockRequest('include=children&fields=parent_id');
        Services::injectMock('request', $request);

        $transformer = new ParentTransformer($request);

        $result = $transformer->transform(['id' => 1]);

        $this->assertSame([
            'parent_id' => 1,
            'children'  => ['child_id' => 99, 'status' => 'transformed'],
        ], $result);
    }

    public function testNestedCollectionTransformerDoesNotInheritState(): void
    {
        $request = $this->createMockRequest('include=childrenCollection&fields=parent_id');
        Services::injectMock('request', $request);

        $transformer = new ParentTransformer($request);

        $result = $transformer->transform(['id' => 1]);

        $this->assertSame([
            'parent_id'          => 1,
            'childrenCollection' => [
                ['child_id' => 77, 'status' => 'transformed'],
                ['child_id' => 88, 'status' => 'transformed'],
            ],
        ], $result);
    }

    public function testBareNestedInstantiationDoesNotInheritState(): void
    {
        // Reproduces the exact leak vector: a child created with a bare
        // `new ChildTransformer()` (no request passed) inside an include
        // method must not pick up the root request's scope from request().
        $request = $this->createMockRequest('include=children&fields=parent_id');
        Services::injectMock('request', $request);

        $root = new ParentTransformer();

        $result = $root->transform(['id' => 5]);

        $this->assertSame([
            'parent_id' => 5,
            'children'  => ['child_id' => 99, 'status' => 'transformed'],
        ], $result);
    }

    public function testNestedTransformerHonorsExplicitRequest(): void
    {
        // A child created with an explicitly passed request must honor that
        // request's scope even while nested - the isolation only suppresses
        // the implicit global fallback, not deliberate developer intent.
        $request = $this->createMockRequest('include=explicitChild&fields=child_id,parent_id');
        Services::injectMock('request', $request);

        $transformer = new ParentTransformer($request);

        $result = $transformer->transform(['id' => 1]);

        $this->assertSame([
            'parent_id'     => 1,
            'explicitChild' => ['child_id' => 99],
        ], $result);
    }

    public function testRootScopeStillAppliesAfterNesting(): void
    {
        // Sanity check that the root transformer keeps applying its own scope
        // while nested children are isolated.
        $request = $this->createMockRequest('include=children&fields=parent_id');
        Services::injectMock('request', $request);

        $transformer = new ParentTransformer($request);

        $result = $transformer->transform(['id' => 1, 'secret' => 'hidden']);

        // Root keeps only parent_id (plus the include key), the child is intact.
        $this->assertArrayHasKey('parent_id', $result);
        $this->assertArrayNotHasKey('secret', $result);
        $this->assertSame(['child_id' => 99, 'status' => 'transformed'], $result['children']);
    }

    public function testDepthIsRestoredAfterIncludeThrows(): void
    {
        $request = $this->createMockRequest('include=nonexistent');
        Services::injectMock('request', $request);

        $throwingRoot = new class ($request) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        try {
            $throwingRoot->transform(['id' => 1, 'name' => 'Test']);
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException) {
            // expected
        }

        // The nesting depth must be balanced after the exception, so a fresh
        // root transformer still applies the request scope (depth back to 0).
        $fieldsRequest = $this->createMockRequest('fields=id');
        Services::injectMock('request', $fieldsRequest);

        $nextRoot = new class ($fieldsRequest) extends BaseTransformer {
            public function toArray(mixed $resource): array
            {
                return $resource;
            }
        };

        $result = $nextRoot->transform(['id' => 1, 'name' => 'Test']);

        $this->assertSame(['id' => 1], $result);
    }

    public function testBareNestedTransformerStillUsedByChildTransformerDirectly(): void
    {
        // When ChildTransformer is itself the root (no parent), it must apply
        // request scope as usual - the isolation only affects nesting.
        $request = $this->createMockRequest('fields=child_id');
        Services::injectMock('request', $request);

        $transformer = new ChildTransformer($request);

        $result = $transformer->transform(['id' => 42]);

        $this->assertSame(['child_id' => 42], $result);
    }
}
