<?php

namespace App\Controllers\Crud;

use CodeIgniter\Controller;

class Crud extends Controller {

    protected $db;
    
    public function __construct(...$params) {
        parent::__construct(...$params);
        $this->db = \Config\Database::connect();
        $fields = $this->db->getFieldData('categories');
    }

    function index() {
        print_r($this->primary_field('categories'));
    }

    function table_list() {
        $tables = $this->db->listTables();

        foreach ($tables as $table) {
            $fields[] = array('table_name' => $table);
        }
        return $fields;
    }

    function primary_field($table) {
//        $fields = $this->db->getFieldData($table);

        foreach ($fields as $field) {
            if ($field->primary_key == 1) {
                return $field->name;
            }
        }
    }

    function not_primary_field($table) {
        $fields = $this->db->getFieldData($table);

        foreach ($fields as $field) {
            if ($field->primary_key == 1)
                continue;
            $fields[] = array('column_name' => $field->name, 'column_key' => 'FK', 'data_type' => $field->type);
        }
        return $fields;
    }

    function all_field($table) {
        $fields = $this->db->getFieldData($table);

        foreach ($fields as $field) {
            $fields[] = array('column_name' => $field->name, 'column_key' => 'FK', 'data_type' => $field->type);
        }
        return $fields;
    }

}
