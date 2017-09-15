<?php

namespace App\Controllers\Generators\Controller;

use App\Controllers;

class Generator extends Controllers\Generators\BaseGenerator {

    protected $options = [
        'cache_type' => 'null',
        'backup_cache' => 'null',
        'ajax_notices' => 'true',
        'lang_file' => 'null',
        'model' => NULL,
        'themed' => FALSE,
        'base_class' => 'BaseController',
        'base_path' => 'Myth\Controllers\\'
    ];
    protected $db;

    //--------------------------------------------------------------------
    public function __construct(...$params) {
        parent::__construct(...$params);

        $this->db = \Config\Database::connect();
    }

    public function index($name) {
        $name = ucfirst($name);

        $data = [
            'controller_name' => $name,
            'today' => date('Y-m-d H:ia')
        ];

        $data = array_merge($data, $this->options);

        if ($data['themed'] == 'y' || $data['themed'] === true) {
            $data['base_class'] = 'ThemedController';
        }

        $destination = $this->determineOutputPath('controllers') . $name . '.php';

        if (!$this->copyTemplate('controller', $destination, $data, $this->overwrite))
            return TRUE;
        // Model?
        $this->options['model'] = empty($options['model']) ? 'algo' : $options['model'];

        // Format per CI
        if (!empty($this->options['model']) && substr($this->options['model'], - 5) !== 'Model') {
            $this->options['model'] .= 'Model';
        }
        $this->options['model'] = !empty($this->options['model']) ? ucfirst($this->options['model']) : NULL;

        print_r($this->getFieldsFromModel('categories'));
    }

    //--------------------------------------------------------------------
    //--------------------------------------------------------------------

    /**
     * Generates the standard views for our CRUD methods.
     */
    protected function createViews($name) {
        helper('inflector');
        $data = [
            'name' => $name,
            'lower_name' => strtolower($name),
            'single_name' => singular($name),
            'plural_name' => plural($name),
            'fields' => $this->prepareFields()
        ];

        $subfolder = empty($this->module) ? '/' . strtolower($name) : '/' . $data['lower_name'];

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

        $fields = explode(' ', $fields);

        $new_fields = [];

        foreach ($fields as $field) {
            $pop = [NULL, NULL, NULL];
            list( $field, $type, $size ) = array_merge(explode(':', $field), $pop);
            $type = strtolower($type);

            // Ignore list
            if (in_array($field, ['created_on', 'modified_on'])) {
                continue;
            }

            // Strings
            if (in_array($type, ['char', 'varchar', 'string'])) {
                $new_fields[] = [
                    'name' => $field,
                    'type' => 'text'
                ];
            }

            // Textarea
            else if ($type == 'text') {
                $new_fields[] = [
                    'name' => $field,
                    'type' => 'textarea'
                ];
            }

            // Number
            else if (in_array($type, ['tinyint', 'int', 'bigint', 'mediumint', 'float', 'double', 'number'])) {
                $new_fields[] = [
                    'name' => $field,
                    'type' => 'number'
                ];
            }

            // Date
            else if (in_array($type, ['date', 'datetime', 'time'])) {
                $new_fields[] = [
                    'name' => $field,
                    'type' => $type
                ];
            }
        }

        return $new_fields;
    }

    //--------------------------------------------------------------------

    private function getFieldsFromModel($modelName) {
        $fullModelName = '\\' . ucfirst($modelName) . 'Model';
        $model = new $fullModelName();

        echo $model->getTable();
        if (!$this->db->tableExists($model->getTable())) {
            return '';
        }

        $fields = $this->db->getFieldData($model->getTable());

        $return = '';

        // Prepare the fields in a string format like
        // it would have been passed on the CLI
        foreach ($fields as $field) {
            $temp = $field->name . ':' . $field->type;

            if (!empty($field->max_length)) {
                $temp .= ':' . $field->max_length;
            }

            $return .= ' ' . $temp;
        }
echo $return;
        return $return;
    }

    //--------------------------------------------------------------------
}
