<?php

namespace App\Controllers\Generators\Controller;

use App\Controllers;

class Generator extends Controllers\Generators\BaseGenerator {

    protected $options = [
        'model' => 'Categories',
        'base_class' => 'BaseController',
        'base_path' => 'Myth\Controllers\\'
    ];
    protected $db;

    //--------------------------------------------------------------------
    public function __construct(...$params) {
        parent::__construct(...$params);

        $this->db = \Config\Database::connect();
        // Format per CI
        if (!empty($this->options['model']) && substr($this->options['model'], - 5) !== 'Model') {
            $this->options['model'] .= 'Model';
        }

    }

    public function index($name) {
        $name = ucfirst($name);

        $data = [
            'controller_name' => $name,
            'today' => date('Y-m-d H:ia')
        ];

        $data = array_merge($data, $this->options);

        $destination = $this->determineOutputPath('controllers') . $name . '.php';

        if (!$this->copyTemplate('controller', $destination, $data, $this->overwrite))
            return TRUE;

//        d($this->getFieldsFromModel($this->options['model']));
        $this->prepareFields();
    }

    //--------------------------------------------------------------------
    //--------------------------------------------------------------------

    /**
     * Generates the standard views for our CRUD methods.
     */
    public function createViews($name) {
        helper('inflector');
        $data = [
            'name' => $name,
            'lower_name' => strtolower($name),
            'single_name' => singular($name),
            'plural_name' => plural($name),
            'fields' => $this->prepareFields()
        ];

        $subfolder = '/' . $data['lower_name'];

        // Index
        $destination = $this->determineOutputPath('views' . $subfolder) . 'index.php';
        $this->copyTemplate('view_index', $destination, $data, $this->overwrite);

        // Create
        $destination = $this->determineOutputPath('views' . $subfolder) . 'create.php';
        $this->copyTemplate('view_create', $destination, $data, $this->overwrite);

        // Show
        $destination = $this->determineOutputPath('views' . $subfolder) . 'show.php';
        $this->copyTemplate('view_show', $destination, $data, $this->overwrite);

        // Index
        $destination = $this->determineOutputPath('views' . $subfolder) . 'update.php';
        $this->copyTemplate('view_update', $destination, $data, $this->overwrite);
    }

    //--------------------------------------------------------------------

    /**
     * Grabs the fields from the CLI options and gets them ready for
     * use within the views.
     */
    protected function prepareFields() {
        if (empty($fields)) {
            // If we have a model, we can get our fields from there
            if (!empty($this->options['model'])) {
                $fields = $this->getFieldsFromModel($this->options['model']);

                if (empty($fields)) {
                    return NULL;
                }
            } else {
                return NULL;
            }
        }

        d($fields);
        $new_fields = [];

        foreach ($fields as $field) {
            
            $type = strtolower($field->type);

            // Ignore list
            if (in_array($field->name, ['created_on', 'modified_on'])) {
                continue;
            }

            // Strings
            if (in_array($type, ['char', 'character', 'character varying', 'varchar', 'string'])) {
                $new_fields[] = [
                    'name' => $field->name,
                    'type' => 'text'
                ];
            }

            // Textarea
            else if ($type == 'text') {
                $new_fields[] = [
                    'name' => $field->name,
                    'type' => 'textarea'
                ];
            }

            // Number
            else if (in_array($type, ['tinyint', 'int', 'integer', 'bigint', 'mediumint', 'float', 'double', 'number'])) {
                $new_fields[] = [
                    'name' => $field->name,
                    'type' => 'number'
                ];
            }

            // Date
            else if (in_array($type, ['date', 'datetime', 'time'])) {
                $new_fields[] = [
                    'name' => $field->name,
                    'type' => $type
                ];
            }
        }
      
        d($new_fields);
        return $new_fields;
    }

    //--------------------------------------------------------------------

    private function getFieldsFromModel($modelName) {
        $fullModelName = '\\' . $modelName;
        $model = new $fullModelName();

        if (!$this->db->tableExists($model->getTable())) {
            return '';
        }

        $fields = $this->db->getFieldData($model->getTable());

        return $fields;
    }

    //--------------------------------------------------------------------
}
