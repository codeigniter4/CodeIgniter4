<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Validation\Validation;
use Config\Database;
use Config\Services;
use InvalidArgumentException;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @no-final
 *
 * @group DatabaseLive
 */
class DatabaseRelatedRulesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected Validation $validation;
    protected array $config = [
        'ruleSets' => [
            Rules::class,
            FormatRules::class,
            FileRules::class,
            CreditCardRules::class,
            TestRules::class,
        ],
        'groupA' => [
            'foo' => 'required|min_length[5]',
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();
    }

    protected function createRules()
    {
        return new Rules();
    }

    public function testIsUniqueFalse(): void
    {
        Database::connect()
            ->table('user')
            ->insert([
                'name'    => 'Derek Travis',
                'email'   => 'derek@world.com',
                'country' => 'Elbonia',
            ]);

        $data = ['email' => 'derek@world.com'];
        $this->validation->setRules(['email' => 'is_unique[user.email]']);
        $this->assertFalse($this->validation->run($data));
    }

    public function testIsUniqueTrue(): void
    {
        $data = ['email' => 'derek@world.co.uk'];
        $this->validation->setRules(['email' => 'is_unique[user.email]']);
        $this->assertTrue($this->validation->run($data));
    }

    public function testIsUniqueWithInvalidDBGroup(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalidGroup is not a valid database connection group');

        $this->validation->setRules(['email' => 'is_unique[user.email]']);
        $data = ['email' => 'derek@world.co.uk'];
        $this->assertTrue($this->validation->run($data, null, 'invalidGroup'));
    }

    public function testIsUniqueWithIgnoreValue(): void
    {
        $db = Database::connect();
        $db
            ->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);
        $row = $db->table('user')
            ->limit(1)
            ->get()
            ->getRow();

        $data = ['email' => 'derek@world.co.uk'];
        $this->validation->setRules(['email' => "is_unique[user.email,id,{$row->id}]"]);
        $this->assertTrue($this->validation->run($data));
    }

    public function testIsUniqueWithIgnoreValuePlaceholder(): void
    {
        $this->hasInDatabase('user', [
            'name'    => 'Derek',
            'email'   => 'derek@world.co.uk',
            'country' => 'GB',
        ]);

        $row = Database::connect()
            ->table('user')
            ->limit(1)
            ->get()
            ->getRow();

        $data = [
            'id'    => $row->id,
            'email' => 'derek@world.co.uk',
        ];

        $this->validation->setRules([
            'id'    => 'is_natural_no_zero',
            'email' => 'is_unique[user.email,id,{id}]',
        ]);
        $this->assertTrue($this->validation->run($data));
    }

    public function testIsUniqueByManualRun(): void
    {
        Database::connect()
            ->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);

        $this->assertFalse($this->createRules()->is_unique('deva@example.com', 'user.email,id,{id}', []));
    }

    public function testIsUniqueIntValueByManualRun(): void
    {
        Database::connect()
            ->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);

        $result = $this->createRules()->is_unique(1, 'user.id', []);

        $this->assertFalse($result);
    }

    public function testIsNotUniqueFalse(): void
    {
        Database::connect()
            ->table('user')
            ->insert([
                'name'    => 'Derek Travis',
                'email'   => 'derek@world.com',
                'country' => 'Elbonia',
            ]);

        $data = ['email' => 'derek1@world.com'];
        $this->validation->setRules(['email' => 'is_not_unique[user.email]']);
        $this->assertFalse($this->validation->run($data));
    }

    public function testIsNotUniqueTrue(): void
    {
        Database::connect()
            ->table('user')
            ->insert([
                'name'    => 'Derek Travis',
                'email'   => 'derek@world.com',
                'country' => 'Elbonia',
            ]);

        $data = ['email' => 'derek@world.com'];
        $this->validation->setRules(['email' => 'is_not_unique[user.email]']);
        $this->assertTrue($this->validation->run($data));
    }

    public function testIsNotUniqueIgnoresParams(): void
    {
        $db = Database::connect();
        $db->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);

        $row = $db->table('user')
            ->limit(1)
            ->get()
            ->getRow();

        $data = ['email' => 'derek@world.co.uk'];
        $this->validation->setRules(['email' => "is_not_unique[user.email,id,{$row->id}]"]);
        $this->assertFalse($this->validation->run($data));
    }

    public function testIsNotUniqueIgnoresParamsPlaceholders(): void
    {
        $this->hasInDatabase('user', [
            'name'    => 'Derek',
            'email'   => 'derek@world.co.uk',
            'country' => 'GB',
        ]);

        $row = Database::connect()
            ->table('user')
            ->limit(1)
            ->get()
            ->getRow();

        $data = [
            'id'    => $row->id,
            'email' => 'derek@world.co.uk',
        ];
        $this->validation->setRules([
            'id'    => 'is_natural_no_zero',
            'email' => 'is_not_unique[user.email,id,{id}]',
        ]);
        $this->assertTrue($this->validation->run($data));
    }

    public function testIsNotUniqueByManualRun(): void
    {
        Database::connect()
            ->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);

        $this->assertTrue($this->createRules()->is_not_unique('deva@example.com', 'user.email,id,{id}', []));
    }
}
