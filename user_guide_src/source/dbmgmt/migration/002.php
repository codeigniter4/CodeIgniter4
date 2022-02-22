<?php

public function up()
{
    $this->db->disableForeignKeyChecks()

    // Migration rules would go here..

    $this->db->enableForeignKeyChecks();
}
