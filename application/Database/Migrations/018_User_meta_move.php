<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add the table for user_meta
 * Move all current meta fields over to the new table
 *
 * Note: This migration does not attempt to restore the previous state of the database
 * when uninstalling, despite attempting to take a backup in the installation
 */
class Migration_User_meta_move extends Migration
{
	/****************************************************************
	 * Table names
	 */
	/**
	 * @var string The name of the users table
	 */
	private $table_name = 'users';

	/**
	 * @var string The name of the users meta table
	 */
	private $meta_table = 'user_meta';

	/**
	 * @var string The name of the settings table
	 */
	private $settings_table = 'settings';

	/****************************************************************
	 * Field definitions
	 */
	/**
	 * @var array The names of the core fields for the users table
	 */
	private $core_user_fields = array(
		'id',
		'role_id',
		'email',
		'username',
		'password_hash',
		'reset_hash',
		'salt',
		'last_login',
		'last_ip',
		'created_on',
		'deleted',
		'banned',
		'ban_message',
		'reset_by',
	);

	/**
	 * @var array Default fields to be copied into the Meta table from the Users
	 */
	private $default_fields = array(
		'first_name',
		'last_name',
		'street_1',
		'street_2',
		'city',
		'zipcode',
		'state_code',
		'country_iso',
	);

	/**
	 * @var array The fields for the Meta table
	 */
	private $meta_fields = array(
		'meta_id'	=> array(
			'type'				=> 'INT',
			'constraint'		=> 20,
			'unsigned'			=> true,
			'auto_increment'	=> true,
			'null'				=> false,
		),
		'user_id'	=> array(
			'type'				=> 'INT',
			'constraint'		=> 20,
			'unsigned'			=> true,
			'default'			=> 0,
			'null'				=> false,
		),
		'meta_key'	=> array(
			'type'				=> 'varchar',
			'constraint'		=> 255,
			'default'			=> '',
			'null'				=> false,
		),
		'meta_value' => array(
			'type'				=> 'text',
			'null'				=> true,
		)
	);

	/**
	 * @var array New fields to be added to the users table
	 */
	private $user_new_fields = array(
		'display_name'	=> array(
			'type'			=> 'varchar',
			'constraint'	=> 255,
			'default'		=> '',
			'null'			=> true,
		),
		'display_name_changed'	=> array(
			'type'			=> 'date',
			'null'			=> true,
		),
	);

	/****************************************************************
	 * Data to Insert
	 */
	/**
	 * @var array Data to be inserted into the settings table
	 */
	private $settings_data = array(
		array(
			'name'		=> 'auth.allow_name_change',
			'module'	=> 'core',
			'value'		=> 1,
		),
		array(
			'name'		=> 'auth.name_change_frequency',
			'module'	=> 'core',
			'value'		=> 1,
		),
		array(
			'name'		=> 'auth.name_change_limit',
			'module'	=> 'core',
			'value'		=> 1,
		),
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		if ( ! $this->db->table_exists($this->meta_table))
		{
			$this->dbforge->add_field($this->meta_fields);
			$this->dbforge->add_key('meta_id', TRUE);
			$this->dbforge->create_table($this->meta_table);
		}

		/*
			Backup our users table

			...assuming BFPATH is set. It's not in the installer,
			but we don't actually want a backup for fresh installs
		*/
		// Only MySQL supports backups currently
		if (defined('BFPATH') && $this->db->dbdriver == 'mysql')
		{
			$filename = APPPATH . '/db/backups/backup_meta_users_table.txt';

			$this->load->dbutil();

			$prefs = array(
				'tables'		=> $this->db->dbprefix . 'users',
				'format'		=> 'txt',
				'filename'		=> $filename,
				'add_drop'		=> true,
				'add_insert'	=> true,
			);

			$backup = $this->dbutil->backup($prefs);

			$this->load->helper('file');
			write_file($filename, $backup);

			if (file_exists($filename))
			{
				log_message('info', 'Backup file successfully saved. It can be found at <a href="/'. $filename .'">'. $filename . '</a>.');
			}
			else
			{
				log_message('error', 'There was a problem saving the backup file.');
				die('There was a problem saving the backup file.');
			}
		}

		/*
			Move User data to meta table
		*/

		// If there are users, loop through them and create meta entries
		// then remove all 'non-core' columns as they will now be in the meta table.
		if ($this->db->count_all_results($this->table_name))
		{
			$query = $this->db->get($this->table_name);
			$rows = $query->result();
			$meta_data = array();

			foreach ($rows as $row)
			{
				foreach ($this->default_fields as $field)
				{
					// We don't want to store the field if it doesn't exist in the user profile.
					if ( ! empty($row->$field))
					{
						$meta_data[] = array(
							'user_id'		=> $row->id,
							'meta_key'		=> $field,
							'meta_value'	=> $row->$field,
						);
					}
				}
			}
			$query->free_result();

			if ( ! empty($meta_data))
			{
				$this->db->insert_batch($this->meta_table, $meta_data);
			}

			unset($meta_data, $rows);
		}

		// $this->db->list_fields uses $this->result_id, which may be
		// incorrect due to insert_batch() or free_result() above,
		// so we run a quick query against the correct table to fix
		// the result_id
		$query = $this->db->get_where($this->table_name, array('id' => 0));

		// Drop existing columns from users table.
		$fields = $query->list_fields();
		foreach ($fields as $field)
		{
			if ( ! in_array($field, $this->core_user_fields))
			{
				$this->dbforge->drop_column($this->table_name, $field);
			}
		}
		unset($fields);
		$query->free_result();

		// Add new fields to users table
		$this->dbforge->add_column($this->table_name, $this->user_new_fields);

		// Add new settings
		$this->db->insert_batch($this->settings_table, $this->settings_data);
	}

	/**
	 * Uninstall this migration
	 * Note: this method does not fully support reverting to
	 * anything near the previous state of the database
	 */
	public function down()
	{
		// Delete the new settings
		$settings_names = array();
		foreach ($this->settings_data as $setting)
		{
			$settings_names[] = $setting['name'];
		}
		if ( ! empty($settings_names))
		{
			$this->db->where_in('name', $settings_names)
				->delete($this->settings_table);
		}

		// Drop the new columns from the users table
		foreach ($this->user_new_fields as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->table_name, $column_name);
		}

		// TODO: Copy the information back over to the users table.

		$this->dbforge->drop_table($this->meta_table);
	}
}
