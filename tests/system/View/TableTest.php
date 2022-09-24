<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\Database\MySQLi\Result;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockTable;
use stdClass;

/**
 * @internal
 */
final class TableTest extends CIUnitTestCase
{
    private Table $table;
    private string $styleTableOne = 'background:#F99;text-align:right;width:5em;';
    private string $styleTableTwo = 'background:cyan;color:white;text-align:right;width:5em;';

    protected function setUp(): void
    {
        $this->table = new MockTable();
    }

    // Setter Methods

    public function testSetTemplate()
    {
        $this->assertFalse($this->table->setTemplate('not an array'));

        $template = ['a' => 'b'];

        $this->table->setTemplate($template);
        $this->assertSame($template, $this->table->template);
    }

    public function testSetEmpty()
    {
        $this->table->setEmpty('nada');
        $this->assertSame('nada', $this->table->emptyCells);
    }

    public function testSetCaption()
    {
        $this->table->setCaption('awesome cap');
        $this->assertSame('awesome cap', $this->table->caption);
    }

    /**
     * @depends testPrepArgs
     */
    public function testSetHeading()
    {
        // uses _prep_args internally, so we'll just do a quick
        // check to verify that func_get_args and prep_args are
        // being called.

        $this->table->setHeading('name', 'color', 'size');

        $this->assertSame(
            [
                ['data' => 'name'],
                ['data' => 'color'],
                ['data' => 'size'],
            ],
            $this->table->heading
        );
    }

    public function testSetFooting()
    {
        // uses _prep_args internally, so we'll just do a quick
        // check to verify that func_get_args and prep_args are
        // being called.

        $subtotal = 12345;

        $this->table->setFooting('Subtotal', $subtotal);

        $this->assertSame(
            [
                ['data' => 'Subtotal'],
                ['data' => $subtotal],
            ],
            $this->table->footing
        );
    }

    public function testSetHeadingWithStyle()
    {
        $template = [
            'heading_cell_start' => '<td>',
            'heading_cell_end'   => '</td>',
        ];

        $this->table->setTemplate($template);
        $this->table->setHeading([['data' => 'Name', 'class' => 'tdh'], ['data' => 'Amount', 'class' => 'tdf', 'style' => $this->styleTableOne]]);

        $this->assertSame(
            [
                [
                    'data'  => 'Name',
                    'class' => 'tdh',
                ],
                [
                    'data'  => 'Amount',
                    'class' => 'tdf',
                    'style' => $this->styleTableOne,
                ],
            ],
            $this->table->heading
        );
    }

    public function testSetFootingWithStyle()
    {
        $template = [
            'footing_cell_start' => '<td>',
            'footing_cell_end'   => '</td>',
        ];

        $this->table->setTemplate($template);
        $this->table->setFooting([['data' => 'Total', 'class' => 'tdf'], ['data' => 3, 'class' => 'tdh', 'style' => $this->styleTableTwo]]);

        $this->assertSame(
            [
                [
                    'data'  => 'Total',
                    'class' => 'tdf',
                ],
                [
                    'data'  => 3,
                    'class' => 'tdh',
                    'style' => $this->styleTableTwo,
                ],
            ],
            $this->table->footing
        );
    }

    /**
     * @depends testPrepArgs
     */
    public function testAddRow()
    {
        // uses _prep_args internally, so we'll just do a quick
        // check to verify that func_get_args and prep_args are
        // being called.

        $this->table->addRow('my', 'pony', 'sings');
        $this->table->addRow('your', 'pony', 'stinks');
        $this->table->addRow('my pony', '>', 'your pony');

        $this->assertCount(3, $this->table->rows);

        $this->assertSame(
            [
                ['data' => 'your'],
                ['data' => 'pony'],
                ['data' => 'stinks'],
            ],
            $this->table->rows[1]
        );
    }

    // Uility Methods

    public function testPrepArgs()
    {
        $expected = [
            ['data' => 'name'],
            ['data' => 'color'],
            ['data' => 'size'],
        ];

        $this->assertSame(
            $expected,
            $this->table->prepArgs(['name', 'color', 'size'])
        );

        // with cell attributes
        // need to add that new argument row to our expected outcome
        $expected[] = [
            'data'  => 'weight',
            'class' => 'awesome',
        ];

        $this->assertSame(
            $expected,
            $this->table->prepArgs(['name', 'color', 'size', ['data' => 'weight', 'class' => 'awesome']])
        );
    }

    public function testDefaultTemplateKeys()
    {
        $keys = [
            'table_open',
            'thead_open',
            'thead_close',
            'heading_row_start',
            'heading_row_end',
            'heading_cell_start',
            'heading_cell_end',
            'tbody_open',
            'tbody_close',
            'row_start',
            'row_end',
            'cell_start',
            'cell_end',
            'row_alt_start',
            'row_alt_end',
            'cell_alt_start',
            'cell_alt_end',
            'table_close',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $this->table->defaultTemplate());
        }
    }

    public function testCompileTemplate()
    {
        $this->assertFalse($this->table->setTemplate('invalid_junk'));

        // non default key
        $this->table->setTemplate(['nonsense' => 'foo']);
        $this->table->compileTemplate();

        $this->assertArrayHasKey('nonsense', $this->table->template);
        $this->assertSame('foo', $this->table->template['nonsense']);

        // override default
        $this->table->setTemplate(['table_close' => '</table junk>']);
        $this->table->compileTemplate();

        $this->assertArrayHasKey('table_close', $this->table->template);
        $this->assertSame('</table junk>', $this->table->template['table_close']);
    }

    public function testMakeColumns()
    {
        // Test bogus parameters
        $this->assertFalse($this->table->makeColumns('invalid_junk'));
        $this->assertFalse($this->table->makeColumns([]));
        $this->assertFalse($this->table->makeColumns(['one', 'two'], '2.5'));

        // Now on to the actual column creation

        $fiveValues = [
            'Laura',
            'Red',
            '15',
            'Katie',
            'Blue',
        ];

        // No column count - no changes to the array
        $this->assertSame(
            $fiveValues,
            $this->table->makeColumns($fiveValues)
        );

        // Column count of 3 leaves us with one &nbsp;
        $this->assertSame(
            [
                ['Laura', 'Red', '15'],
                ['Katie', 'Blue', '&nbsp;'],
            ],
            $this->table->makeColumns($fiveValues, 3)
        );
    }

    public function testClear()
    {
        $this->table->setHeading('Name', 'Color', 'Size');

        // Make columns changes auto_heading
        $rows = $this->table->makeColumns([
            'Laura',
            'Red',
            '15',
            'Katie',
            'Blue',
        ], 3);

        foreach ($rows as $row) {
            $this->table->addRow($row);
        }

        $this->assertFalse($this->table->autoHeading);
        $this->assertCount(3, $this->table->heading);
        $this->assertCount(2, $this->table->rows);

        $this->table->clear();

        $this->assertTrue($this->table->autoHeading);
        $this->assertEmpty($this->table->heading);
        $this->assertEmpty($this->table->rows);
    }

    public function testSetFromArray()
    {
        $data = [
            [
                'name',
                'color',
                'number',
            ],
            [
                'Laura',
                'Red',
                '22',
            ],
            [
                'Katie',
                'Blue',
            ],
        ];

        $this->table->autoHeading = false;
        $this->table->setFromArray($data);
        $this->assertEmpty($this->table->heading);

        $this->table->clear();

        $this->table->setFromArray($data);
        $this->assertCount(2, $this->table->rows);

        $expected = [
            ['data' => 'name'],
            ['data' => 'color'],
            ['data' => 'number'],
        ];

        $this->assertSame($expected, $this->table->heading);

        $expected = [
            ['data' => 'Katie'],
            ['data' => 'Blue'],
        ];

        $this->assertSame($expected, $this->table->rows[1]);
    }

    public function testSetFromObject()
    {
        // This needs to be passed by reference to CI_DB_result::__construct()
        $dummy           = new stdClass();
        $dummy->connID   = null;
        $dummy->resultID = null;

        $DBResult = new DBResultDummy($dummy->connID, $dummy->resultID);

        $this->table->setFromDBResult($DBResult);

        $expected = [
            ['data' => 'name'],
            ['data' => 'email'],
        ];

        $this->assertSame($expected, $this->table->heading);

        $expected = [
            'name'  => ['data' => 'Foo Bar'],
            'email' => ['data' => 'foo@bar.com'],
        ];

        $this->assertSame($expected, $this->table->rows[1]);
    }

    public function testGenerate()
    {
        // Prepare the data
        $data = [
            [
                'Name',
                'Color',
                'Size',
            ],
            [
                'Fred',
                'Blue',
                'Small',
            ],
            [
                [
                    'data'  => 'Mary',
                    'class' => 'cssMary',
                ],
                [
                    'data'  => 'Red',
                    'class' => 'cssRed',
                ],
                [
                    'data'  => 'Large',
                    'class' => 'cssLarge',
                ],
            ],
            [
                'John',
                'Green',
                'Medium',
            ],
        ];

        $this->table->setCaption('Awesome table');

        $subtotal = 12345;

        $this->table->setFooting('Subtotal', $subtotal);

        $table = $this->table->generate($data);

        // Test the table header
        $this->assertStringContainsString('<th>Name</th>', $table);
        $this->assertStringContainsString('<th>Color</th>', $table);
        $this->assertStringContainsString('<th>Size</th>', $table);

        // Test the first entry
        $this->assertStringContainsString('<td>Fred</td>', $table);
        $this->assertStringContainsString('<td>Blue</td>', $table);
        $this->assertStringContainsString('<td>Small</td>', $table);

        // Test entry with attribute
        $this->assertStringContainsString('<td class="cssMary">Mary</td>', $table);
        $this->assertStringContainsString('<td class="cssRed">Red</td>', $table);
        $this->assertStringContainsString('<td class="cssLarge">Large</td>', $table);

        // Check for the caption
        $this->assertStringContainsString('<caption>Awesome table</caption>', $table);

        // Test the table footing
        $this->assertStringContainsString('<td>Subtotal</td>', $table);
        $this->assertStringContainsString('<td>12345</td>', $table);
    }

    public function testGenerateTdWithClassStyle()
    {
        $template = [
            'table_open'         => '<table border="1" cellpadding="4" cellspacing="0">',
            'thead_open'         => '<thead>',
            'thead_close'        => '</thead>',
            'heading_row_start'  => '<tr>',
            'heading_row_end'    => '</tr>',
            'heading_cell_start' => '<td>',
            'heading_cell_end'   => '</td>',
            'tfoot_open'         => '<tfoot>',
            'tfoot_close'        => '</tfoot>',
            'footing_row_start'  => '<tr>',
            'footing_row_end'    => '</tr>',
            'footing_cell_start' => '<td>',
            'footing_cell_end'   => '</td>',
            'tbody_open'         => '<tbody>',
            'tbody_close'        => '</tbody>',
            'row_start'          => '<tr>',
            'row_end'            => '</tr>',
            'cell_start'         => '<td>',
            'cell_end'           => '</td>',
            'row_alt_start'      => '<tr>',
            'row_alt_end'        => '</tr>',
            'cell_alt_start'     => '<td>',
            'cell_alt_end'       => '</td>',
            'table_close'        => '</table>',
        ];

        $this->table->setTemplate($template);
        $this->table->setHeading([['data' => 'Name', 'class' => 'tdk'], ['data' => 'Amount', 'class' => 'tdr', 'style' => $this->styleTableOne]]);

        $this->table->addRow(['Fred', 1]);
        $this->table->addRow(['Mary', 3]);
        $this->table->addRow(['John', 6]);

        $this->table->setFooting([['data' => 'Total', 'class' => 'thk'], ['data' => '<small class="text-light">IDR <span class="badge badge-info">10</span></small>', 'class' => 'thr', 'style' => $this->styleTableTwo]]);

        $table = $this->table->generate();

        // Header
        $this->assertStringContainsString('<td class="tdk">Name</td>', $table);
        $this->assertStringContainsString('<td style="' . $this->styleTableOne . '" class="tdr">Amount</td>', $table);

        // Footer
        $this->assertStringContainsString('<td class="thk">Total</td>', $table);
        $this->assertStringContainsString('<td style="' . $this->styleTableTwo . '" class="thr"><small class="text-light">IDR <span class="badge badge-info">10</span></small></td>', $table);
    }

    public function testGenerateThWithClassStyle()
    {
        $template = [
            'table_open'         => '<table border="1" cellpadding="4" cellspacing="0">',
            'thead_open'         => '<thead>',
            'thead_close'        => '</thead>',
            'heading_row_start'  => '<tr>',
            'heading_row_end'    => '</tr>',
            'heading_cell_start' => '<th>',
            'heading_cell_end'   => '</th>',
            'tfoot_open'         => '<tfoot>',
            'tfoot_close'        => '</tfoot>',
            'footing_row_start'  => '<tr>',
            'footing_row_end'    => '</tr>',
            'footing_cell_start' => '<th>',
            'footing_cell_end'   => '</th>',
            'tbody_open'         => '<tbody>',
            'tbody_close'        => '</tbody>',
            'row_start'          => '<tr>',
            'row_end'            => '</tr>',
            'cell_start'         => '<td>',
            'cell_end'           => '</td>',
            'row_alt_start'      => '<tr>',
            'row_alt_end'        => '</tr>',
            'cell_alt_start'     => '<td>',
            'cell_alt_end'       => '</td>',
            'table_close'        => '</table>',
        ];

        $this->table->setTemplate($template);
        $this->table->setHeading([['data' => 'Name', 'class' => 'tdk'], ['data' => 'Amount', 'class' => 'tdr', 'style' => $this->styleTableOne]]);

        $this->table->addRow(['Fred', 1]);
        $this->table->addRow(['Mary', 3]);
        $this->table->addRow(['John', 6]);

        $this->table->setFooting([['data' => 'Total', 'class' => 'thk'], ['data' => '<small class="text-light">IDR <span class="badge badge-info">10</span></small>', 'class' => 'thr', 'style' => $this->styleTableTwo]]);

        $table = $this->table->generate();

        // Header
        $this->assertStringContainsString('<th class="tdk">Name</th>', $table);
        $this->assertStringContainsString('<th style="' . $this->styleTableOne . '" class="tdr">Amount</th>', $table);

        // Footer
        $this->assertStringContainsString('<th class="thk">Total</th>', $table);
        $this->assertStringContainsString('<th style="' . $this->styleTableTwo . '" class="thr"><small class="text-light">IDR <span class="badge badge-info">10</span></small></th>', $table);
    }

    public function testGenerateInvalidHeadingFooting()
    {
        $template = [
            'table_open'         => '<table border="1" cellpadding="4" cellspacing="0">',
            'thead_open'         => '<thead>',
            'thead_close'        => '</thead>',
            'heading_row_start'  => '<tr>',
            'heading_row_end'    => '</tr>',
            'heading_cell_start' => '<header>',
            'heading_cell_end'   => '</header>',
            'tfoot_open'         => '<tfoot>',
            'tfoot_close'        => '</tfoot>',
            'footing_row_start'  => '<tr>',
            'footing_row_end'    => '</tr>',
            'footing_cell_start' => '<footer>',
            'footing_cell_end'   => '</footer>',
            'tbody_open'         => '<tbody>',
            'tbody_close'        => '</tbody>',
            'row_start'          => '<tr>',
            'row_end'            => '</tr>',
            'cell_start'         => '<td>',
            'cell_end'           => '</td>',
            'row_alt_start'      => '<tr>',
            'row_alt_end'        => '</tr>',
            'cell_alt_start'     => '<td>',
            'cell_alt_end'       => '</td>',
            'table_close'        => '</table>',
        ];

        $this->table->setTemplate($template);
        $this->table->setHeading([['data' => 'Name', 'class' => 'tdk'], ['data' => 'Amount', 'class' => 'tdr', 'style' => $this->styleTableOne]]);

        $this->table->addRow(['Fred', 1]);
        $this->table->addRow(['Mary', 3]);
        $this->table->addRow(['John', 6]);

        $this->table->setFooting([['data' => 'Total', 'class' => 'thk'], ['data' => '<small class="text-light">IDR <span class="badge badge-info">10</span></small>', 'class' => 'thr', 'style' => $this->styleTableTwo]]);

        $table = $this->table->generate();

        // Header
        $this->assertStringContainsString('<header>Name</header>', $table);
        $this->assertStringContainsString('<header>Amount</header>', $table);

        // Footer
        $this->assertStringContainsString('<footer>Total</footer>', $table);
        $this->assertStringContainsString('<footer><small class="text-light">IDR <span class="badge badge-info">10</span></small></footer>', $table);
    }

    public function testGenerateInvalidHeadingFootingHTML()
    {
        $template = [
            'table_open'         => '<table border="1" cellpadding="4" cellspacing="0">',
            'thead_open'         => '<thead>',
            'thead_close'        => '</thead>',
            'heading_row_start'  => '<tr>',
            'heading_row_end'    => '</tr>',
            'heading_cell_start' => 'th>',
            'heading_cell_end'   => '</th>',
            'tfoot_open'         => '<tfoot>',
            'tfoot_close'        => '</tfoot>',
            'footing_row_start'  => '<tr>',
            'footing_row_end'    => '</tr>',
            'footing_cell_start' => 'td>',
            'footing_cell_end'   => '</td>',
            'tbody_open'         => '<tbody>',
            'tbody_close'        => '</tbody>',
            'row_start'          => '<tr>',
            'row_end'            => '</tr>',
            'cell_start'         => '<td>',
            'cell_end'           => '</td>',
            'row_alt_start'      => '<tr>',
            'row_alt_end'        => '</tr>',
            'cell_alt_start'     => '<td>',
            'cell_alt_end'       => '</td>',
            'table_close'        => '</table>',
        ];

        $this->table->setTemplate($template);
        $this->table->setHeading([['data' => 'Name', 'class' => 'tdk'], ['data' => 'Amount', 'class' => 'tdr', 'style' => $this->styleTableOne]]);

        $this->table->addRow(['Fred', 1]);
        $this->table->addRow(['Mary', 3]);
        $this->table->addRow(['John', 6]);

        $this->table->setFooting([['data' => 'Total', 'class' => 'thk'], ['data' => '<small class="text-light">IDR <span class="badge badge-info">10</span></small>', 'class' => 'thr', 'style' => $this->styleTableTwo]]);

        $table = $this->table->generate();

        // Header
        $this->assertStringContainsString('th>Name</th>', $table);
        $this->assertStringContainsString('th>Amount</th>', $table);

        // Footer
        $this->assertStringContainsString('td>Total</td>', $table);
        $this->assertStringContainsString('td><small class="text-light">IDR <span class="badge badge-info">10</span></small></td>', $table);
    }

    public function testGenerateEmptyCell()
    {
        // Prepare the data
        $data = [
            [
                'Name',
                'Color',
                'Size',
            ],
            [
                'Fred',
                'Blue',
                'Small',
            ],
            [
                'Mary',
                'Red',
                null,
            ],
            [
                'John',
                'Green',
                'Medium',
            ],
        ];

        $this->table->setCaption('Awesome table');
        $this->table->setEmpty('Huh?');
        $table = $this->table->generate($data);

        $this->assertStringContainsString('<td>Huh?</td>', $table);
    }

    public function testWithConfig()
    {
        $customSettings = [
            'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="mytable">',
        ];

        $table = new Table($customSettings);

        // Prepare the data
        $data = [
            [
                'Name',
                'Color',
                'Size',
            ],
            [
                'Fred',
                'Blue',
                'Small',
            ],
            [
                'Mary',
                'Red',
                'Large',
            ],
            [
                'John',
                'Green',
                'Medium',
            ],
        ];

        $generated = $table->generate($data);

        $this->assertStringContainsString('<table border="1" cellpadding="2" cellspacing="1" class="mytable">', $generated);
    }

    public function testGenerateFromDBResult()
    {
        // This needs to be passed by reference to CI_DB_result::__construct()
        $dummy           = new stdClass();
        $dummy->connID   = null;
        $dummy->resultID = null;
        $DBResult        = new DBResultDummy($dummy->connID, $dummy->resultID);

        $table = $this->table->generate($DBResult);

        // Test the table header
        $this->assertStringContainsString('<th>name</th>', $table);
        $this->assertStringContainsString('<th>email</th>', $table);

        // Test the first entry
        $this->assertStringContainsString('<td>John Doe</td>', $table);
        $this->assertStringContainsString('<td>foo@bar.com</td>', $table);
    }

    public function testUndefined()
    {
        // Prepare the data
        $data = [];

        $table = $this->table->generate($data);

        $this->assertSame('Undefined table data', $table);
    }

    public function testCallback()
    {
        $this->table->setHeading('Name', 'Color', 'Size');
        $this->table->addRow('Fred', '<strong>Blue</strong>', 'Small');

        $this->table->function = 'htmlspecialchars';

        $generated = $this->table->generate();

        $this->assertStringContainsString('<td>Fred</td><td>&lt;strong&gt;Blue&lt;/strong&gt;</td><td>Small</td>', $generated);
    }

    public function testInvalidCallback()
    {
        $this->table->setHeading('Name', 'Color', 'Size');
        $this->table->addRow('Fred', '<strong>Blue</strong>', 'Small');

        $this->table->function = 'ticklemyfancy';

        $generated = $this->table->generate();

        $this->assertStringContainsString('<td>Fred</td><td><strong>Blue</strong></td><td>Small</td>', $generated);
    }
}

// We need this for the _set_from_db_result() test
class DBResultDummy extends Result
{
    public function getFieldNames(): array
    {
        return [
            'name',
            'email',
        ];
    }

    public function getResultArray(): array
    {
        return [
            [
                'name'  => 'John Doe',
                'email' => 'john@doe.com',
            ],
            [
                'name'  => 'Foo Bar',
                'email' => 'foo@bar.com',
            ],
        ];
    }
}
