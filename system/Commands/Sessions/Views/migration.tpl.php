@php namespace <?= $namespace ?>\Database\Migrations;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2017 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	2014-2017 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

use CodeIgniter\Database\Migration;

class Migration_create_<?= $tableName ?>_table extends Migration
{
<?php if (isset($DBGroup)) : ?>
	protected $DBGroup = '<?= $DBGroup ?>';
<?php endif ?>

	public function up()
	{
		$this->forge->addField([
			'id'         => [
				'type'       => 'VARCHAR',
				'constraint' => 128,
				'null'       => false
			],
			'ip_address' => [
				'type'       => 'VARCHAR',
				'constraint' => 45,
				'null'       => false
			],
			'timestamp'  => [
				'type'       => 'INT',
				'constraint' => 10,
				'unsigned'   => true,
				'null'       => false,
				'default'    => 0
			],
			'data'       => [
				'type'       => 'TEXT',
				'null'       => false,
				'default'    => ''
			],
		]);
	<?php if ($matchIP === true) : ?>
	$this->forge->addKey(['id', 'ip_address'], true);
	<?php else: ?>
	$this->forge->addKey('id', true);
	<?php endif ?>
	$this->forge->addKey('timestamp');
		$this->forge->createTable('<?= $tableName ?>', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('<?= $tableName ?>', true);
	}
}
