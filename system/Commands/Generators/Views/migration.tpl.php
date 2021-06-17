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
            'id'         => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'timestamp'  => ['type' => 'INT', 'unsigned' => true, 'null' => false, 'default' => 0],
            'data'       => ['type' => 'TEXT', 'null' => false, 'default' => ''],
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
