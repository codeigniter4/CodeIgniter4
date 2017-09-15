<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
	In 0.7, we are moving from the salted password generation that we had
	to use the phpass 0.3 Password Hasing algorithm.
	
	This does make it impossible to convert the passwords but we will 
	make it so that the users must change their password on next login.
*/
class Migration_Converting_auth_to_phpass extends Migration
{

	//--------------------------------------------------------------------

	public function up()
	{
		$this->load->dbforge();
		
		// We no longer need the 'salt' field
		$this->dbforge->drop_column('users', 'salt');
		
		// We do need to store the number of iterations used, though.
		$fields = array(
			'password_iterations' => array(
				'type'			=> 'int',
				'constraint'	=> 4,
				'null'			=> false
			)
		);
		$this->dbforge->add_column('users', $fields);
		
		// And we need to change the size of the password hash column
		$fields = array(
			'password_hash'	=> array(
				'type'			=> 'char',
				'constraint'	=> 60
			),
		);
		$this->dbforge->modify_column('users', $fields);
		
		// Add a force_password_update column
		$fields = array(
			'force_password_reset'	=> array(
				'type'			=> 'tinyint',
				'constraint'	=> 1,
				'default'		=> 0
			),
		);
		$this->dbforge->add_column('users', $fields);
		
		// Set all users to have their passwords reset
		$this->db->where('force_password_reset', 0)->update('users', array('force_password_reset' => 1));
		
		// Add the default password_iterations to the settingns table
		// Do it by hand so it still works in the installer
		$data = array(
			'name'		=> 'password_iterations',
			'module'	=>	'users',
			'value'		=> 8
		);
		$this->db->insert('settings', $data);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->load->dbforge();
		
		// Add the 'salt' column back
		$field = array(
			'salt' => array(
				'type'	=> 'varchar',
				'constraint'	=> 7,
			)
		);
		$this->dbforge->add_column('users', $field);
		
		// Drop the password_iterations column
		$this->dbforge->drop_column('users', 'password_iterations');
		
		// Reshape the password_hash column
		$fields = array(
			'password_hash'	=> array(
				'type'			=> 'varchar',
				'constraint'	=> 40
			),
		);
		$this->dbforge->modify_column('users', $fields);
		
		// Remove the force_password_reset column
		$this->dbforge->drop_column('users', 'force_password_reset');
		
		// Remove the password_iterations setting
		$this->load->library('settings/settings_lib');
		$this->settings_lib->delete('password_iterations', 'users');
	}

	//--------------------------------------------------------------------

}
