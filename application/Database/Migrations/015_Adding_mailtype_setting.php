<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add a mail type setting to the database
 */
class Migration_Adding_mailtype_setting extends Migration
{
	/**
	 * @var string The name of the table
	 */
	private $table_name = 'settings';

	/**
	 * @var array The data to insert into the table
	 */
	private $data = array(
		'name' => 'mailtype',
		'module' => 'email',
		'value' => 'text',
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$this->db->insert($this->table_name, $this->data);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$delete_data = $this->data['name'];

		if ( ! empty($delete_data))
		{
			$this->db->where('name', $delete_data)
				->delete($this->table_name);
		}
	}
}