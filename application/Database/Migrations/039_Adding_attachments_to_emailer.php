<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Migration_Adding_attachments_to_emailer extends Migration
{

    /**
     * @var string The name of the Email Queue table
     */
    private $email_table = 'email_queue';
    
    private $csv_column = 'csv_attachment';

    public function up()
    {

        $fields = array(
            $this->csv_column => array(
                'type' => 'TEXT',
                'null' => TRUE
            )
        );
        $this->dbforge->add_column($this->email_table, $fields);
    }

    //--------------------------------------------------------------------

    public function down()
    {
        $this->dbforge->drop_column($this->email_table, $this->csv_column);
    }

    //--------------------------------------------------------------------
}
