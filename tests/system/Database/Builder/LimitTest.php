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

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\OCI8\Builder as OCI8Builder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class LimitTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testLimitAlone(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testLimitAndOffset(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5, 1);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testLimitAndOffsetMethod(): void
    {
        $builder = new BaseBuilder('user', $this->db);

        $builder->limit(5)->offset(1);

        $expectedSQL = 'SELECT * FROM "user"  LIMIT 1, 5';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideOracle11gVersions')]
    public function testLimitAloneWithOracle11g(string $version): void
    {
        $builder = new OCI8Builder('user', $this->createOci8Connection($version));

        $builder->limit(5);

        $expectedSQL = 'SELECT * FROM (SELECT INNER_QUERY.*, ROWNUM RNUM FROM (SELECT * FROM "user" ) INNER_QUERY WHERE ROWNUM < 6)';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideOracle11gVersions')]
    public function testLimitAndOffsetWithOracle11g(string $version): void
    {
        $builder = new OCI8Builder('user', $this->createOci8Connection($version));

        $builder->limit(5, 1);

        $expectedSQL = 'SELECT * FROM (SELECT INNER_QUERY.*, ROWNUM RNUM FROM (SELECT * FROM "user" ) INNER_QUERY WHERE ROWNUM < 7) WHERE RNUM >= 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideOracle11gVersions')]
    public function testLimitAndOffsetWithOrderByOracle11g(string $version): void
    {
        $builder = new OCI8Builder('user', $this->createOci8Connection($version));

        $builder->orderBy('id', 'ASC')->limit(5, 1);

        $expectedSQL = 'SELECT * FROM (SELECT INNER_QUERY.*, ROWNUM RNUM FROM (SELECT * FROM "user" ORDER BY "id" ASC ) INNER_QUERY WHERE ROWNUM < 7) WHERE RNUM >= 1';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideOracle12_1AndAboveVersions')]
    public function testLimitAloneWithOracle12_1AndAbove(string $version): void
    {
        $builder = new OCI8Builder('user', $this->createOci8Connection($version));

        $builder->limit(5);

        $expectedSQL = 'SELECT * FROM "user"  ORDER BY 1 OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideOracle12_1AndAboveVersions')]
    public function testLimitAndOffsetWithOracle12_1AndAbove(string $version): void
    {
        $builder = new OCI8Builder('user', $this->createOci8Connection($version));

        $builder->limit(5, 1);

        $expectedSQL = 'SELECT * FROM "user"  ORDER BY 1 OFFSET 1 ROWS FETCH NEXT 5 ROWS ONLY';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    #[DataProvider('provideOracle12_1AndAboveVersions')]
    public function testLimitAndOffsetWithOrderByOracle12_1AndAbove(string $version): void
    {
        $builder = new OCI8Builder('user', $this->createOci8Connection($version));

        $builder->orderBy('id', 'ASC')->limit(5, 1);

        $expectedSQL = 'SELECT * FROM "user" ORDER BY "id" ASC  OFFSET 1 ROWS FETCH NEXT 5 ROWS ONLY';

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideOracle11gVersions(): iterable
    {
        yield '11.2' => ['11.2.0.4.0'];

        yield '11.1' => ['11.1.0.7.0'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideOracle12_1AndAboveVersions(): iterable
    {
        yield '12.1' => ['12.1'];

        yield '12.1.0.2' => ['12.1.0.2.0'];

        yield '19c' => ['19.0.0.0.0'];
    }

    private function createOci8Connection(string $version): MockConnection
    {
        return new class (['DBDriver' => 'OCI8'], $version) extends MockConnection {
            private readonly string $oracleVersion;

            public function __construct(array $params, string $version)
            {
                parent::__construct($params);

                $this->oracleVersion = $version;
            }

            public function getVersion(): string
            {
                return $this->oracleVersion;
            }
        };
    }
}
