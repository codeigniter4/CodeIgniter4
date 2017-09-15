<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Create the settings table in the database
 * Populate the table with the default settings
 */
class Migration_Move_settings_into_db extends Migration
{
	/**
	 * @var string Name of the Settings table
	 */
	private $table_name = 'settings';

	/**
	 * @var array Fields for the Settings table
	 */
	private $fields = array(
		'name' => array(
			'type' => 'VARCHAR',
			'constraint' => 30,
			'null' => false,
		),
		'module' => array(
			'type' => 'VARCHAR',
			'constraint' => 50,
			'null' => false,
		),
		'value' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
			'null' => false,
		),
	);

	/**
	 * @var array Data to insert into the Settings table
	 */
	private $data = array(
		array(
			'name' => 'site.title',
			'module' => 'core',
			'value' => '',
		),
		array(
			'name' => 'site.system_email',
			'module' => 'core',
			'value' => '',
		),
		array(
			'name' => 'site.status',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'site.list_limit',
			'module' => 'core',
			'value' => '25',
		),
		array(
			'name' => 'site.show_profiler',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'site.show_front_profiler',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'updates.do_check',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'updates.bleeding_edge',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'auth.allow_register',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'auth.login_type',
			'module' => 'core',
			'value' => 'email',
		),
		array(
			'name' => 'auth.use_usernames',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'auth.allow_remember',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'auth.remember_length',
			'module' => 'core',
			'value' => '1209600',
		),
		array(
			'name' => 'auth.do_login_redirect',
			'module' => 'core',
			'value' => '1',
		),
		array(
			'name' => 'auth.use_extended_profile',
			'module' => 'core',
			'value' => '0',
		),
		array(
			'name' => 'sender_email',
			'module' => 'email',
			'value' => '',
		),
		array(
			'name' => 'protocol',
			'module' => 'email',
			'value' => 'mail',
		),
		array(
			'name' => 'mailpath',
			'module' => 'email',
			'value' => '/usr/sbin/sendmail',
		),
		array(
			'name' => 'smtp_host',
			'module' => 'email',
			'value' => '',
		),
		array(
			'name' => 'smtp_user',
			'module' => 'email',
			'value' => '',
		),
		array(
			'name' => 'smtp_pass',
			'module' => 'email',
			'value' => '',
		),
		array(
			'name' => 'smtp_port',
			'module' => 'email',
			'value' => '',
		),
		array(
			'name' => 'smtp_timeout',
			'module' => 'email',
			'value' => '',
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
		if ($this->db->table_exists($this->table_name))
		{
			$this->dbforge->drop_table($this->table_name);
		}

		$this->dbforge->add_field($this->fields);
		$this->dbforge->add_key('name', true);
		$this->dbforge->create_table($this->table_name);

		$this->db->insert_batch($this->table_name, $this->data);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		if ($this->db->table_exists($this->table_name))
		{
			$this->dbforge->drop_table($this->table_name);
		}
	}
}