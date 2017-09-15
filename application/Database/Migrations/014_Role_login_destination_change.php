<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Removing the '/' from the Role login_destination field in the DB so that
 * the user will be brought to the last requested url when they login
 */
class Migration_Role_login_destination_change extends Migration
{
	/**
	 * @var string The name of the table to update
	 */
	private $table_name = 'roles';

	/**
	 * @var string The name of the field to update
	 */
	private $field = 'login_destination';

	/**
	 * @var string The value we are replacing
	 */
	private $old_val = '/';

	/**
	 * @var string The new value
	 */
	private $new_val = '';

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// change the roles which don't have any specific login_destination set
		$this->db->where($this->field, $this->old_val)
			->update($this->table_name, array($this->field => $this->new_val));
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// change the roles which don't have any specific login_destination set
		$this->db->where($this->field, $this->new_val)
			->update($this->table_name, array($this->field => $this->old_val));
	}
}