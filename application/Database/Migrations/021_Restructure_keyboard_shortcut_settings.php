<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Change the structure of keyboard shortcut settings in the database
 */
class Migration_Restructure_keyboard_shortcut_settings extends Migration
{
	/**
	 * @var string The name of the settings table
	 */
	private $table_name = 'settings';

	/**
	 * @var array The data we are replacing in the settings table
	 */
	private $field = array(
		'name' => 'ui.shortcut_keys',
	);

	/**
	 * @var array The data we are adding to the settings table
	 */
	private $new_field = array(
		'module' => 'core.ui',
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// get the current keyboard shortcuts
		$query = $this->db->where($this->field)->get($this->table_name);

		if ($query->num_rows() > 0)
		{
			// divide them up
			$setting_obj = $query->row();
			$keys = unserialize($setting_obj->value);

			$new_keys = array();
			foreach ($keys as $name => $shortcut)
			{
				$new_keys[] = array(
					'name'   => $name,
					'module' => $this->new_field['module'],
					'value'  => $shortcut,
				);
			}

			// store them individually
			if (count($new_keys))
			{
				// insert the new keys into the db
				if ($this->db->insert_batch($this->table_name, $new_keys))
				{
					// delete the old entry
					$this->db->where($this->field)->delete($this->table_name);
				}
			}
		}
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// THIS MAY NOT WORK
		// depending on the number of shortcuts you have the size of the "value" field
		// in the settings table might not be big enough to store all of your shortcuts
		// in one setting record and could give an sql error

		// get the current keyboard shortcuts
		$query = $this->db->where($this->new_field)->get($this->table_name);

		if ($query->num_rows() > 0)
		{
			// combine them
			$new_keys = array();
			foreach ($query->result() as $key)
			{
				$new_keys[$key->name] = $key->value;
			}

			// store keys in one setting record
			if (count($new_keys))
			{
				$rec = array(
					'name'   => $this->field['name'],
					'module' => 'core',
					'value'  => serialize($new_keys),
				);
				// insert the new keys into the db
				if ($this->db->insert($this->table_name, $rec))
				{
					// delete the old entry
					$this->db->where($this->new_field)->delete($this->table_name);
				}
			}
		}
	}
}