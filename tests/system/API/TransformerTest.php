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
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class TransformerTest extends CIUnitTestCase
{
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

            protected function includePosts(): array
            {
                return [['id' => 1, 'title' => 'Post 1']];
            }

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
}
