<?php

namespace CodeIgniter\Validation;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Config\Services;
use stdClass;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class RulesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    /**
     * @var Validation
     */
    protected $validation;
    protected $config = [
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

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();

        $_FILES = [];
    }

    //--------------------------------------------------------------------
    // Rules Tests
    //--------------------------------------------------------------------

    public function testRequiredNull()
    {
        $data = [
            'foo' => null,
        ];

        $this->validation->setRules([
            'foo' => 'required|alpha',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testRequiredTrueString()
    {
        $data = [
            'foo' => 123,
        ];

        $this->validation->setRules([
            'foo' => 'required',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testRequiredFalseString()
    {
        $data = [
            'foo' => null,
            'bar' => 123,
        ];

        $this->validation->setRules([
            'foo' => 'required',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testRequiredTrueArray()
    {
        $data = [
            'foo' => [123],
        ];

        $this->validation->setRules([
            'foo' => 'required',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testRequiredFalseArray()
    {
        $data = [
            'foo' => [],
        ];

        $this->validation->setRules([
            'foo' => 'required',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testRequiredObject()
    {
        $data = [
            'foo' => new stdClass(),
        ];

        $this->validation->setRules([
            'foo' => 'required',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider ifExistProvider
     *
     * @param $rules
     * @param $data
     * @param $expected
     */
    public function testIfExist($rules, $data, $expected)
    {
        $this->validation->setRules($rules);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function ifExistProvider()
    {
        return [
            [
                ['foo' => 'required'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'required'],
                ['foo' => null],
                false,
            ],
            [
                ['foo' => 'if_exist|required'],
                ['foo' => ''],
                false,
            ],
            // Input data does not exist then the other rules will be ignored
            [
                ['foo' => 'if_exist|required'],
                [],
                true,
            ],
            // Testing for multi-dimensional data
            [
                ['foo.bar' => 'if_exist|required'],
                ['foo' => ['bar' => '']],
                false,
            ],
            [
                ['foo.bar' => 'if_exist|required'],
                ['foo' => []],
                true,
            ],
        ];
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider emptysProvider
     *
     * @param $rules
     * @param $data
     * @param $expected
     */
    public function testEmptys($rules, $data, $expected)
    {
        $this->validation->setRules($rules);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function emptysProvider()
    {
        return [
            [
                ['foo' => 'permit_empty'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => '0'],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => 0.0],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => null],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => false],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 'user@domain.tld'],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 'invalid'],
                false,
            ],
            // Required has more priority
            [
                ['foo' => 'permit_empty|required|valid_email'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => null],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => false],
                false,
            ],
            // This tests will return true because the input data is trimmed
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => '0'],
                true,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => 0.0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => 0],
                true,
            ],
            [
                [
                    'foo' => 'permit_empty|required_with[bar]',
                ],
                [
                    'foo' => 0.0,
                    'bar' => 1,
                ],
                true,
            ],
            [
                [
                    'foo' => 'permit_empty|required_with[bar]',
                ],
                [
                    'foo' => '',
                    'bar' => 1,
                ],
                false,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => 0],
                true,
            ],
            [
                [
                    'foo' => 'permit_empty|required_without[bar]',
                ],
                [
                    'foo' => 0.0,
                    'bar' => 1,
                ],
                true,
            ],
            [
                [
                    'foo' => 'permit_empty|required_without[bar]',
                ],
                [
                    'foo' => '',
                    'bar' => 1,
                ],
                true,
            ],
        ];
    }

    //--------------------------------------------------------------------

    public function testMatchesNull()
    {
        $data = [
            'foo' => null,
            'bar' => null,
        ];

        $this->validation->setRules([
            'foo' => 'matches[bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMatchesTrue()
    {
        $data = [
            'foo' => 'match',
            'bar' => 'match',
        ];

        $this->validation->setRules([
            'foo' => 'matches[bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMatchesFalse()
    {
        $data = [
            'foo' => 'match',
            'bar' => 'nope',
        ];

        $this->validation->setRules([
            'foo' => 'matches[bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMatcheNestedsTrue()
    {
        $data = [
            'nested' => [
                'foo' => 'match',
                'bar' => 'match',
            ],
        ];

        $this->validation->setRules([
            'nested.foo' => 'matches[nested.bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMatchesNestedFalse()
    {
        $data = [
            'nested' => [
                'foo' => 'match',
                'bar' => 'nope',
            ],
        ];

        $this->validation->setRules([
            'nested.foo' => 'matches[nested.bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testDiffersNull()
    {
        $data = [
            'foo' => null,
            'bar' => null,
        ];

        $this->validation->setRules([
            'foo' => 'differs[bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testDiffersTrue()
    {
        $data = [
            'foo' => 'match',
            'bar' => 'nope',
        ];

        $this->validation->setRules([
            'foo' => 'differs[bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testDiffersFalse()
    {
        $data = [
            'foo' => 'match',
            'bar' => 'match',
        ];

        $this->validation->setRules([
            'foo' => 'differs[bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testDiffersNestedTrue()
    {
        $data = [
            'nested' => [
                'foo' => 'match',
                'bar' => 'nope',
            ],
        ];

        $this->validation->setRules([
            'nested.foo' => 'differs[nested.bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testDiffersNestedFalse()
    {
        $data = [
            'nested' => [
                'foo' => 'match',
                'bar' => 'match',
            ],
        ];

        $this->validation->setRules([
            'nested.foo' => 'differs[nested.bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testEqualsNull()
    {
        $data = [
            'foo' => null,
        ];

        $this->validation->setRules([
            'foo' => 'equals[]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testEqualsEmptyIsEmpty()
    {
        $data = [
            'foo' => '',
        ];

        $this->validation->setRules([
            'foo' => 'equals[]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testEqualsReturnsFalseOnFailure()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'equals[notbar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testEqualsReturnsTrueOnSuccess()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'equals[bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testEqualsReturnsFalseOnCaseMismatch()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'equals[Bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testNotEqualsNull()
    {
        $data = [
            'foo' => null,
        ];

        $this->validation->setRules([
            'foo' => 'not_equals[]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testNotEqualsEmptyIsEmpty()
    {
        $data = [
            'foo' => '',
        ];

        $this->validation->setRules([
            'foo' => 'not_equals[]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testNotEqualsReturnsFalseOnFailure()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'not_equals[bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testNotEqualsReturnsTrueOnSuccess()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'not_equals[notbar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testNotEqualsReturnsTrueOnCaseMismatch()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'not_equals[Bar]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsUniqueFalse()
    {
        $db = Database::connect();
        $db->table('user')
            ->insert([
                'name'    => 'Derek Travis',
                'email'   => 'derek@world.com',
                'country' => 'Elbonia',
            ]);

        $data = [
            'email' => 'derek@world.com',
        ];

        $this->validation->setRules([
            'email' => 'is_unique[user.email]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsUniqueTrue()
    {
        $data = [
            'email' => 'derek@world.co.uk',
        ];

        $this->validation->setRules([
            'email' => 'is_unique[user.email]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsUniqueIgnoresParams()
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

        $data = [
            'email' => 'derek@world.co.uk',
        ];

        $this->validation->setRules([
            'email' => "is_unique[user.email,id,{$row->id}]",
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsUniqueIgnoresParamsPlaceholders()
    {
        $this->hasInDatabase('user', [
            'name'    => 'Derek',
            'email'   => 'derek@world.co.uk',
            'country' => 'GB',
        ]);

        $db  = Database::connect();
        $row = $db->table('user')
            ->limit(1)
            ->get()
            ->getRow();

        $data = [
            'id'    => $row->id,
            'email' => 'derek@world.co.uk',
        ];

        $this->validation->setRules([
            'email' => 'is_unique[user.email,id,{id}]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsUniqueByManualRun()
    {
        $db = Database::connect();
        $db->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);

        $this->assertFalse((new Rules())->is_unique('deva@example.com', 'user.email,id,{id}', []));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsNotUniqueFalse()
    {
        $db = Database::connect();
        $db->table('user')
            ->insert([
                'name'    => 'Derek Travis',
                'email'   => 'derek@world.com',
                'country' => 'Elbonia',
            ]);

        $data = [
            'email' => 'derek1@world.com',
        ];

        $this->validation->setRules([
            'email' => 'is_not_unique[user.email]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsNotUniqueTrue()
    {
        $db = Database::connect();
        $db->table('user')
            ->insert([
                'name'    => 'Derek Travis',
                'email'   => 'derek@world.com',
                'country' => 'Elbonia',
            ]);

        $data = [
            'email' => 'derek@world.com',
        ];

        $this->validation->setRules([
            'email' => 'is_not_unique[user.email]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsNotUniqueIgnoresParams()
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

        $data = [
            'email' => 'derek@world.co.uk',
        ];

        $this->validation->setRules([
            'email' => "is_not_unique[user.email,id,{$row->id}]",
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsNotUniqueIgnoresParamsPlaceholders()
    {
        $this->hasInDatabase('user', [
            'name'    => 'Derek',
            'email'   => 'derek@world.co.uk',
            'country' => 'GB',
        ]);

        $db  = Database::connect();
        $row = $db->table('user')
            ->limit(1)
            ->get()
            ->getRow();

        $data = [
            'id'    => $row->id,
            'email' => 'derek@world.co.uk',
        ];

        $this->validation->setRules([
            'email' => 'is_not_unique[user.email,id,{id}]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @group DatabaseLive
     */
    public function testIsNotUniqueByManualRun()
    {
        $db = Database::connect();
        $db->table('user')
            ->insert([
                'name'    => 'Developer A',
                'email'   => 'deva@example.com',
                'country' => 'Elbonia',
            ]);

        $this->assertTrue((new Rules())->is_not_unique('deva@example.com', 'user.email,id,{id}', []));
    }

    //--------------------------------------------------------------------

    public function testMinLengthNull()
    {
        $data = [
            'foo' => null,
        ];

        $this->validation->setRules([
            'foo' => 'min_length[3]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMinLengthReturnsTrueWithSuccess()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'min_length[2]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMinLengthReturnsTrueWithExactLength()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'min_length[3]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMinLengthReturnsFalseWhenWrong()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'min_length[4]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMaxLengthNull()
    {
        $data = [
            'foo' => null,
        ];

        $this->validation->setRules([
            'foo' => 'max_length[1]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMaxLengthReturnsFalseWithNonNumericVal()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'max_length[bar]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMaxLengthReturnsTrueWithSuccess()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'max_length[4]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMaxLengthReturnsTrueWithExactLength()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'max_length[3]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testMaxLengthReturnsFalseWhenWrong()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'max_length[2]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testExactLengthNull()
    {
        $data = [
            'foo' => null,
        ];

        $this->validation->setRules([
            'foo' => 'exact_length[3]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testExactLengthReturnsTrueOnSuccess()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'exact_length[3]',
        ]);

        $this->assertTrue($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testExactLengthDetectsBadLength()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'exact_length[abc]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testExactLengthReturnsFalseWhenShort()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'exact_length[2]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testExactLengthReturnsFalseWhenLong()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->validation->setRules([
            'foo' => 'exact_length[4]',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //-------------------------------------------------------------------

    /**
     * @dataProvider greaterThanProvider
     *
     * @param $str
     * @param $expected
     */
    public function testGreaterThan($first, $second, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => "greater_than[{$second}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function greaterThanProvider()
    {
        return [
            [
                '-10',
                '-11',
                true,
            ],
            [
                '10',
                '9',
                true,
            ],
            [
                '10',
                '10',
                false,
            ],
            [
                '10',
                'a',
                false,
            ],
            [
                '10a',
                '10',
                false,
            ],
            [
                null,
                null,
                false,
            ],
        ];
    }

    //-------------------------------------------------------------------

    /**
     * @dataProvider greaterThanEqualProvider
     *
     * @param $str
     * @param $expected
     */
    public function testGreaterThanEqual($first, $second, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => "greater_than_equal_to[{$second}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function greaterThanEqualProvider()
    {
        return [
            [
                '0',
                '0',
                true,
            ],
            [
                '1',
                '0',
                true,
            ],
            [
                '-1',
                '0',
                false,
            ],
            [
                '10a',
                '0',
                false,
            ],
            [
                null,
                null,
                false,
            ],
            [
                1,
                null,
                true,
            ],
            [
                null,
                1,
                false,
            ],
        ];
    }

    //-------------------------------------------------------------------

    /**
     * @dataProvider lessThanProvider
     *
     * @param $str
     * @param $expected
     */
    public function testLessThan($first, $second, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => "less_than[{$second}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function lessThanProvider()
    {
        return [
            [
                '4',
                '5',
                true,
            ],
            [
                '-1',
                '0',
                true,
            ],
            [
                '4',
                '4',
                false,
            ],
            [
                '10a',
                '5',
                false,
            ],
            [
                null,
                null,
                false,
            ],
            [
                1,
                null,
                false,
            ],
            [
                null,
                1,
                false,
            ],
        ];
    }

    //-------------------------------------------------------------------

    /**
     * @dataProvider lessThanEqualProvider
     *
     * @param $str
     * @param $expected
     */
    public function testLessEqualThan($first, $second, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => "less_than_equal_to[{$second}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function lessThanEqualProvider()
    {
        return [
            [
                '-1',
                '0',
                true,
            ],
            [
                '-1',
                '-1',
                true,
            ],
            [
                '4',
                '4',
                true,
            ],
            [
                '0',
                '-1',
                false,
            ],
            [
                '10a',
                '0',
                false,
            ],
            [
                null,
                null,
                false,
            ],
            [
                null,
                1,
                false,
            ],
            [
                1,
                null,
                false,
            ],
        ];
    }

    //-------------------------------------------------------------------

    /**
     * @dataProvider inListProvider
     *
     * @param string $first
     * @param string $second
     * @param bool   $expected
     */
    public function testInList($first, $second, $expected)
    {
        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => "in_list[{$second}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider inListProvider
     *
     * @param string $first
     * @param string $second
     * @param bool   $expected
     */
    public function testNotInList($first, $second, $expected)
    {
        $expected = ! $expected;

        $data = [
            'foo' => $first,
        ];

        $this->validation->setRules([
            'foo' => "not_in_list[{$second}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function inListProvider()
    {
        return [
            [
                'red',
                'red,Blue,123',
                true,
            ],
            [
                'Blue',
                'red, Blue,123',
                true,
            ],
            [
                'Blue',
                'red,Blue,123',
                true,
            ],
            [
                '123',
                'red,Blue,123',
                true,
            ],
            [
                'Red',
                'red,Blue,123',
                false,
            ],
            [
                ' red',
                'red,Blue,123',
                false,
            ],
            [
                '1234',
                'red,Blue,123',
                false,
            ],
            [
                null,
                'red,Blue,123',
                false,
            ],
            [
                'red',
                null,
                false,
            ],
        ];
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider requiredWithProvider
     *
     * @param $check
     * @param $expected
     */
    public function testRequiredWith($field, $check, $expected = false)
    {
        $data = [
            'foo'   => 'bar',
            'bar'   => 'something',
            'baz'   => null,
            'array' => [
                'nonEmptyField1' => 'value1',
                'nonEmptyField2' => 'value2',
                'emptyField1'    => null,
                'emptyField2'    => null,
            ],
        ];

        $this->validation->setRules([
            $field => "required_with[{$check}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function requiredWithProvider()
    {
        return [
            [
                'nope',
                'bar',
                false,
            ],
            [
                'foo',
                'bar',
                true,
            ],
            [
                'nope',
                'baz',
                true,
            ],
            [
                null,
                null,
                true,
            ],
            [
                null,
                'foo',
                false,
            ],
            [
                'foo',
                null,
                true,
            ],
            [
                'array.emptyField1',
                'array.emptyField2',
                true,
            ],
            [
                'array.nonEmptyField1',
                'array.emptyField2',
                true,
            ],
            [
                'array.emptyField1',
                'array.nonEmptyField2',
                false,
            ],
            [
                'array.nonEmptyField1',
                'array.nonEmptyField2',
                true,
            ],
        ];
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider requiredWithoutProvider
     *
     * @param $check
     * @param $expected
     */
    public function testRequiredWithout($field, $check, $expected = false)
    {
        $data = [
            'foo'   => 'bar',
            'bar'   => 'something',
            'baz'   => null,
            'array' => [
                'nonEmptyField1' => 'value1',
                'nonEmptyField2' => 'value2',
                'emptyField1'    => null,
                'emptyField2'    => null,
            ],
        ];

        $this->validation->setRules([
            $field => "required_without[{$check}]",
        ]);

        $this->assertSame($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function requiredWithoutProvider()
    {
        return [
            [
                'nope',
                'bars',
                false,
            ],
            [
                'foo',
                'nope',
                true,
            ],
            [
                null,
                null,
                false,
            ],
            [
                null,
                'foo',
                true,
            ],
            [
                'foo',
                null,
                true,
            ],
            [
                'array.emptyField1',
                'array.emptyField2',
                false,
            ],
            [
                'array.nonEmptyField1',
                'array.emptyField2',
                true,
            ],
            [
                'array.emptyField1',
                'array.nonEmptyField2',
                true,
            ],
            [
                'array.nonEmptyField1',
                'array.nonEmptyField2',
                true,
            ],
        ];
    }

    //--------------------------------------------------------------------
}
