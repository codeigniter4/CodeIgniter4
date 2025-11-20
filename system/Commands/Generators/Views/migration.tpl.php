<@php

namespace {namespace};

use CodeIgniter\Database\Migration;

class {class} extends Migration
{
<?php if ($session): ?>
    protected $DBGroup = '<?= $DBGroup ?>';

    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
<?php if ($DBDriver === 'MySQLi'): ?>
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            '`timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL',
            'data' => ['type' => 'BLOB', 'null' => false],
 <?php elseif ($DBDriver === 'Postgre'): ?>
            'ip_address inet NOT NULL',
            'timestamp timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL',
            "data bytea DEFAULT '' NOT NULL",
<?php endif; ?>
        ]);
<?php if ($matchIP) : ?>
        $this->forge->addKey(['id', 'ip_address'], true);
<?php else: ?>
        $this->forge->addKey('id', true);
<?php endif ?>
        $this->forge->addKey('timestamp');
        $this->forge->createTable('<?= $table ?>', true);
    }

    public function down()
    {
        $this->forge->dropTable('<?= $table ?>', true);
    }
<?php else: ?>
    public function up()
    {
        //
    }

    public function down()
    {
        //
    }
<?php endif ?>
}
