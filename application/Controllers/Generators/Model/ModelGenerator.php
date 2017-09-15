<?php
/**
 * Sprint
 *
 * A set of power tools to enhance the CodeIgniter framework and provide consistent workflow.
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
 * @package     Sprint
 * @author      Lonnie Ezell
 * @copyright   Copyright 2014-2015, New Myth Media, LLC (http://newmythmedia.com)
 * @license     http://opensource.org/licenses/MIT  (MIT)
 * @link        http://sprintphp.com
 * @since       Version 1.0
 */

use Myth\CLI;

class ModelGenerator extends \Myth\Forge\BaseGenerator {

	protected $options = [
		'table_name'        => '',
		'primary_key'       => '',
		'set_created'       => true,
		'set_modified'      => true,
		'created_field'     => 'created_on',
		'modified_field'    => 'modified_on',
		'date_format'       => 'datetime',
		'log_user'          => false,
		'created_by_field'  => 'created_by',
		'modified_by_field' => 'modified_by',
		'deleted_by_field'  => 'deleted_by',
		'use_soft_deletes'  => true,
		'soft_delete_key'   => 'deleted',
		'protected'         => '',
		'return_type'       => 'array',
		'return_insert_id'  => true,
		'rules'             => '[]'
	];

	//--------------------------------------------------------------------

	public function run( $segments = [ ], $quiet = false )
	{
		$name = array_shift( $segments );

        $options = CLI::getOptions();

		$this->options['table_name'] = array_shift( $segments );

		if ( empty( $name ) )
		{
			$name = CLI::prompt( 'Model name' );
		}

		// Format to CI Standards
		if ( substr( $name, - 6 ) !== '_model' )
		{
			$name .= '_model';
		}
		$name = ucfirst( $name );

		if ( $quiet === false )
		{
			$this->collectOptions( $name, $options );
		}
		else
		{
			$this->quietSetOptions( $name, $options );
		}

		$data = [
			'model_name' => $name,
			'today'      => date( 'Y-m-d H:ia' )
		];

		$data = array_merge( $data, $this->options );

		$destination = $this->determineOutputPath( 'models' ) . $name . '.php';

		if ( ! $this->copyTemplate( 'model', $destination, $data, $this->overwrite ) )
		{
			CLI::error( 'Error creating new files' );
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/*
	 * Customizes our settings
	 */
	protected function collectOptions( $model_name, $options=[] )
	{
		$this->load->helper( 'inflector' );

		// Table Name?
		if ( empty( $this->options['table_name'] ) )
		{
			$this->options['table_name'] = empty( $options['table'] ) ?
				CLI::prompt( 'Table name', plural( strtolower( str_replace( '_model', '', $model_name ) ) ) ) :
				$options['table'];
		}

		$this->options['fields'] = $this->table_info( $this->options['table_name'], $options );

		// Primary Key
		if (empty($this->options['primary_key']))
		{
			$this->options['primary_key'] = empty( $options['primary_key'] ) ?
				CLI::prompt( 'Primary Key', 'id' ) :
				$options['primary_key'];
		}

		$this->options['protected'] = [ $this->options['primary_key'] ];

		// Set Created?
		if ( empty( $options['set_created'] ) )
		{
			$ans = CLI::prompt( 'Set Created date?', [ 'y', 'n' ] );
			if ( $ans == 'n' )
			{
				$this->options['set_created'] = FALSE;
			}
		}

		// Set Modified?
		if ( empty( $options['set_modified'] ) )
		{
			$ans = CLI::prompt( 'Set Modified date?', [ 'y', 'n' ] );
			if ( $ans == 'n' )
			{
				$this->options['set_modified'] = FALSE;
			}
		}

		// Date Format
		$this->options['date_format'] = empty( $options['date_format'] ) ?
			CLI::prompt( 'Date Format?', [ 'datetime', 'date', 'int' ] ) :
			$options['date_format'];

		// Log User?
		if ( empty( $options['log_user'] ) )
		{
			$ans = CLI::prompt( 'Log User actions?', [ 'y', 'n' ] );
			if ( $ans == 'y' )
			{
				$this->options['log_user'] = TRUE;
			}
		}

		// Soft Deletes
		if ( empty( $options['soft_delete'] ) )
		{
			$ans = CLI::prompt( 'Use Soft Deletes?', [ 'y', 'n' ] );
			if ( $ans == 'n' )
			{
				$this->options['soft_delete'] = false;
			}
		}

	}

	//--------------------------------------------------------------------

	protected function quietSetOptions( $model_name, $options=[] )
	{
		$this->load->helper( 'inflector' );

		if (empty($this->options['table_name']))
		{
			$this->options['table_name'] = plural( strtolower( str_replace( '_model', '', $model_name ) ) );
		}

		// Try to set it from the database first,
		// otherwise, try to pull from fields
		$this->options['fields'] = $this->table_info( $this->options['table_name'], $options );

		$this->options['primary_key'] = ! empty( $this->options['primary_key'] ) ? $this->options['primary_key'] : 'id';
		$this->options['protected']   = [ $this->options['primary_key'] ];
	}

	//--------------------------------------------------------------------

	/**
	 * Get the structure and details for the fields in the specified DB table
	 *
	 * @param string $table_name Name of the table to check
	 *
	 * @return mixed An array of fields or false if the table does not exist
	 */
	protected function table_info( $table_name, $options=[] )
	{
		$this->load->database();

		// Check whether the table exists in this database
		if ( ! $this->db->table_exists( $table_name ) )
		{
            if (empty($options['fields']))
            {
                return FALSE;
            }

            $fields = $this->parseFieldString($options['fields']);
		}
        else
        {
            $fields = $this->db->field_data( $table_name );
        }

		// There may be something wrong or the database driver may not return
		// field data
		if ( empty( $fields ) )
		{
			return FALSE;
		}

        $this->options['set_created']       = false;
        $this->options['set_modified']      = false;
        $this->options['use_soft_deletes']  = false;

		// Use the primary key if the table has one already set.
		foreach ( $fields as $field )
		{
			if ( ! empty( $field->primary_key ) && $field->primary_key == 1 )
			{
				$this->options['primary_key'] = $field->name;
			}

            if ($field->name == $this->options['created_field'])
            {
                $this->options['set_created'] = true;
            }

            if ($field->name == $this->options['modified_field'])
            {
                $this->options['set_modified'] = true;
            }

            if ($field->name == $this->options['soft_delete_key'])
            {
                $this->options['use_soft_deletes'] = true;
            }
		}

		// Set our validation rules based on these fields
		$this->options['rules'] = $this->buildValidationRules( $fields );

		return $fields;
	}

	//--------------------------------------------------------------------

    /**
     * Grabs the fields from the CLI options and gets them ready for
     * use within the views.
     */
    protected function parseFieldString($fields)
    {
        if ( empty( $fields ) )
        {
            return NULL;
        }

        $fields = explode( ' ', $fields );

        $new_fields = [ ];

        foreach ( $fields as $field )
        {
            $pop = [ NULL, NULL, NULL ];
            list( $field, $type, $size ) = array_merge( explode( ':', $field ), $pop );
            $type = strtolower( $type );

            // Strings
            if (in_array($type, ['char', 'varchar', 'string']))
            {
                $new_fields[] = [
                    'name'  => $field,
                    'type'  => 'text'
                ];
            }

            // Textarea
            else if ($type == 'text')
            {
                $new_fields[] = [
                    'name'  => $field,
                    'type'  => 'textarea'
                ];
            }

            // Number
            else if (in_array($type, ['tinyint', 'int', 'bigint', 'mediumint', 'float', 'double', 'number']))
            {
                $new_fields[] = [
                    'name'  => $field,
                    'type'  => 'number'
                ];
            }

            // Date
            else if (in_array($type, ['date', 'datetime', 'time']))
            {
                $new_fields[] = [
                    'name'  => $field,
                    'type'  => $type
                ];
            }
        }

        // Convert to objects
        array_walk($new_fields, function(&$item, $key) {
            $item = (object)$item;
        });

        return $new_fields;
    }

    //--------------------------------------------------------------------


	/**
	 * Takes the fields from field_data() and creates the basic validation
	 * rules for those fields.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function buildValidationrules($fields=[])
	{
		if (empty($fields) || ! is_array($fields) || ! count($fields))
		{
			return null;
		}

		$rules = [];

		foreach ($fields as $field)
		{
			$rule = [];

			switch ($field->type)
			{
				// Numeric Types
				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'int':
				case 'integer':
				case 'bigint':
					$rule[] = 'integer';
					break;
				case 'decimal':
				case 'dec':
				case 'numeric':
				case 'fixed':
					$rule[] = 'decimal';
					break;
				case 'float':
				case 'double':
					$rule[] = 'numeric';
					break;

				// Date types don't have many defaults we can go off of...

				// Text Types
				case 'char':
				case 'varchar':
				case 'text':
					$rule[] = 'alpha_numeric_spaces';
					break;
			}

			if (! empty($field->max_length))
			{
				$rule[] = "max_length[{$field->max_length}]";
			}

			$rules[] = [
				'field' => $field->name,
				'label' => ucwords(str_replace('_', ' ', $field->name)),
				'rules' => implode('|', $rule)
			];
		}

		$str = $this->stringify($rules);

		// Clean up the resulting array a bit.
		$str = substr_replace($str, "\n]", -3);

		return $str;
	}

	//--------------------------------------------------------------------

}