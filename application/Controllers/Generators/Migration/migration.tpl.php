<?php

$up     = '';
$down   = '';

//--------------------------------------------------------------------
// Actions
//--------------------------------------------------------------------

/*
 * Create
 */
if ($action == 'create')
{
    $up = "\$fields = {$fields};

        \$this->dbforge->add_field(\$fields);
";

    if (! empty($primary_key))
    {
        $up .= "        \$this->dbforge->add_key('{$primary_key}', true);
";
    }

    $up .="	    \$this->dbforge->create_table('{$table}', true, config_item('migration_create_table_attr') );
    ";

    $down = "\$this->dbforge->drop_table('{$table}');";
}

/*
 * Add
 */
if ($action == 'add' && ! empty($column))
{
    $up = "\$field = {$column_string};
        \$this->dbforge->add_column('{$table}', \$field);";

    $down = "\$this->dbforge->drop_column('{$table}', '{$column}');";
}

/*
 * Remove
 */
if ($action == 'remove' && ! empty($column))
{
    $up = "\$this->dbforge->drop_column('{$table}', '{$column}');";

    $down = "\$field = {$column_string};
        \$this->dbforge->add_column('{$table}', \$field);";
}

//--------------------------------------------------------------------
// The Template
//--------------------------------------------------------------------

echo "<?php

/**
 * Migration: {$clean_name}
 *
 * Created by: SprintPHP
 * Created on: {$today}
 *
 * @property \$dbforge
 */
class Migration_{$name} extends CI_Migration {

    public function up ()
    {
        {$up}
    }

    //--------------------------------------------------------------------

    public function down ()
    {
        {$down}
    }

    //--------------------------------------------------------------------

}";
