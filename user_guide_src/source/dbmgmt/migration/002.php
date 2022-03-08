<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlog extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // Migration rules would go here..

        $this->db->enableForeignKeyChecks();
    }
}
