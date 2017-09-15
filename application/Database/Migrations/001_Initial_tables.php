<?php

namespace App\Database\Migrations;

/**
 * Install the initial tables:
 *	Email Queue
 *	Login Attempts
 *	Permissions
 *	Roles
 *	Sessions
 *	States
 *	Users
 *	User Cookies
 * 
 * \CodeIgniter\Database\Migration
 */
class Migration_Initial_tables extends \CodeIgniter\Database\Migration
{
	/****************************************************************
	 * Table Names
	 */
	/**
	 * @var string The name of the Email Queue table
	 */
	private $email_table = 'email_queue';

	/**
	 * @var string The name of the Login Attempts table
	 */
	private $login_table = 'login_attempts';

	/**
	 * @var string The name of the Permissions table
	 */
	private $permissions_table = 'permissions';

	/**
	 * @var string The name of the Roles table
	 */
	private $roles_table = 'roles';

	/**
	 * @var string The name of the Sessions table
	 */
	private $sessions_table = 'sessions';

	/**
	 * @var string The name of the States table
	 */
	private $states_table = 'states';

	/**
	 * @var string The name of the Users table
	 */
	private $users_table = 'users';

	/**
	 * @var string The name of the User Cookies table
	 */
	private $cookies_table = 'user_cookies';

	/****************************************************************
	 * Field Definitions
	 */
	/**
	 * @var array Fields for the Email table
	 */
	private $email_fields = array(
		'id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'auto_increment' => true,
            'null' => false,
		),
		'to_email' => array(
			'type' => 'VARCHAR',
			'constraint' => 128,
            'null' => false,
		),
		'subject' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
            'null' => false,
		),
		'message' => array(
			'type' => 'TEXT',
            'null' => false,
		),
		'alt_message' => array(
			'type' => 'TEXT',
			'null' => true,
		),
		'max_attempts' => array(
			'type' => 'INT',
			'constraint' => 11,
			'default' => 3,
            'null' => false,
		),
		'attempts' => array(
			'type' => 'INT',
			'constraint' => 11,
			'default' => 0,
            'null' => false,
		),
		'success' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
		'date_published' => array(
			'type' => 'DATETIME',
			'null' => true,
		),
		'last_attempt' => array(
			'type' => 'DATETIME',
			'null' => true,
		),
		'date_sent' => array(
			'type' => 'DATETIME',
			'null' => true,
		),
	);

	/**
	 * @var array Fields for the Login table
	 */
	private $login_fields = array(
		'id' => array(
			'type' => 'BIGINT',
			'constraint' => 20,
			'auto_increment' => true,
            'null' => false,
		),
		'ip_address' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
            'null' => false,
		),
		'login' => array(
			'type' => 'VARCHAR',
			'constraint' => 50,
            'null' => false,
		),
        /* This will probably cause an error outside MySQL and may not
         * be cross-database compatible for reasons other than
         * CURRENT_TIMESTAMP
         */
		'time TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
	);

	/**
	 * @var array Fields for the Permissions table
	 */
	private $permission_fields = array(
		'permission_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'auto_increment' => true,
            'null' => false,
		),
		'role_id' => array(
			'type' => 'INT',
			'constraint' => 11,
            'null' => false,
		),
//		"`Site.Signin.Allow` tinyint(1) NOT NULL DEFAULT '0'",
		'Site_Signin_Allow' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Site.Content.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Site_Content_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Site.Statistics.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Site_Statistics_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Site.Appearance.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Site_Appearance_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Site.Settings.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Site_Settings_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Site.Developer.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Site_Developer_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Roles.Manage` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Roles_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Users.Manage` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Users_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Users.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Users_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Users.Add` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Users_Add' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Database.Manage` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Database_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Emailer.Manage` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Emailer_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Logs.View` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Logs_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
//		"`Bonfire.Logs.Manage` tinyint(1) NOT NULL DEFAULT '0'",
		'Bonfire_Logs_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
	);

	/**
	 * @var array Fields for the roles table
	 */
	private $roles_fields = array(
		'role_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'auto_increment' => true,
            'null' => false,
		),
		'role_name' => array(
			'type' => 'VARCHAR',
			'constraint' => 60,
            'null' => false,
		),
		'description' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
			'null' => true,
		),
		'default' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
		'can_delete' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 1,
            'null' => false,
		),
	);

	/**
	 * @var array Fields for the Sessions table
	 */
	private $sessions_fields = array(
		'session_id' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
			'default' => '0',
            'null' => false,
		),
		'ip_address' => array(
			'type' => 'VARCHAR',
			'constraint' => 16,
			'default' => '0',
            'null' => false,
		),
		'user_agent' => array(
			'type' => 'VARCHAR',
			'constraint' => 50,
            'null' => false,
		),
		'last_activity' => array(
			'type' => 'INT',
			'constraint' => 10,
			'unsigned' => true,
			'default' => 0,
            'null' => false,
		),
		'user_data' => array(
			'type' => 'TEXT',
            'null' => false,
		),
	);

	/**
	 * @var array Fields for the States table
	 */
	private $states_fields = array(
		'id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'auto_increment' => true,
            'null' => false,
		),
		'name' => array(
			'type' => 'CHAR',
			'constraint' => 40,
            'null' => false,
		),
		'abbrev' => array(
			'type' => 'CHAR',
			'constraint' => 2,
            'null' => false,
		),
	);

	/**
	 * @var array Fields for the users table
	 */
	private $users_fields = array(
		'id' => array(
			'type' => 'BIGINT',
			'constraint' => 20,
			'unsigned' => true,
			'auto_increment' => true,
            'null' => false,
		),
		'role_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'default' => 4,
            'null' => false,
		),
		'first_name' => array(
			'type' => 'VARCHAR',
			'constraint' => 20,
			'null' => true,
		),
		'last_name' => array(
			'type' => 'VARCHAR',
			'constraint' => 20,
			'null' => true,
		),
		'email' => array(
			'type' => 'VARCHAR',
			'constraint' => 120,
            'null' => false,
		),
		'username' => array(
			'type' => 'VARCHAR',
			'constraint' => 30,
			'default' => '',
            'null' => false,
		),
		'password_hash' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
            'null' => false,
		),
		'temp_password_hash' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
			'null' => true,
		),
		'salt' => array(
			'type' => 'VARCHAR',
			'constraint' => 7,
            'null' => false,
		),
		'last_login' => array(
			'type' => 'DATETIME',
			'default' => '0000-00-00 00:00:00',
            'null' => false,
		),
		'last_ip' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
			'default' => '',
            'null' => false,
		),
		'created_on' => array(
			'type' => 'DATETIME',
			'default' => '0000-00-00 00:00:00',
            'null' => false,
		),
		'street_1' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
			'null' => true,
		),
		'street_2' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
			'null' => true,
		),
		'city' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
			'null' => true,
		),
		'state_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'null' => true,
		),
		'zipcode' => array(
			'type' => 'INT',
			'constraint' => 7,
			'null' => true,
		),
		'zip_extra' => array(
			'type' => 'INT',
			'constraint' => 5,
			'null' => true,
		),
		'country_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'null' => true,
		),
		'deleted' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
            'null' => false,
		),
	);

	/**
	 * @var array Fields for the Cookies table
	 */
	private $cookies_fields = array(
		'user_id' => array(
			'type' => 'BIGINT',
			'constraint' => 20,
            'null' => false,
		),
		'token' => array(
			'type' => 'VARCHAR',
			'constraint' => 128,
            'null' => false,
		),
		'created_on' => array(
			'type' => 'DATETIME',
            'null' => false,
		),
	);

	/****************************************************************
	 * Data to Insert
	 */
	/**
	 * @var array Default Permissions
	 */
	private $permissions_data = array(
        // Administrator
		array(
			'role_id' => 1,
			'Site_Signin_Allow' => 1,
			'Site_Content_View' => 1,
			'Site_Statistics_View' => 1,
			'Site_Appearance_View' => 1,
			'Site_Settings_View' => 1,
			'Site_Developer_View' => 1,
			'Bonfire_Roles_Manage' => 1,
			'Bonfire_Users_Manage' => 1,
			'Bonfire_Users_View' => 1,
			'Bonfire_Users_Add' => 1,
			'Bonfire_Database_Manage' => 1,
			'Bonfire_Emailer_Manage' => 1,
			'Bonfire_Logs_View' => 1,
			'Bonfire_Logs_Manage' => 1,
		),
        // Editor
		array(
			'role_id' => 2,
			'Site_Signin_Allow' => 1,
			'Site_Content_View' => 1,
			'Site_Statistics_View' => 1,
			'Site_Appearance_View' => 1,
			'Site_Settings_View' => 0,
			'Site_Developer_View' => 0,
			'Bonfire_Roles_Manage' => 0,
			'Bonfire_Users_Manage' => 0,
			'Bonfire_Users_View' => 0,
			'Bonfire_Users_Add' => 0,
			'Bonfire_Database_Manage' => 0,
			'Bonfire_Emailer_Manage' => 0,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
		),
        // Developer
		array(
			'role_id' => 6,
			'Site_Signin_Allow' => 1,
			'Site_Content_View' => 1,
			'Site_Statistics_View' => 1,
			'Site_Appearance_View' => 1,
			'Site_Settings_View' => 1,
			'Site_Developer_View' => 1,
			'Bonfire_Roles_Manage' => 1,
			'Bonfire_Users_Manage' => 1,
			'Bonfire_Users_View' => 1,
			'Bonfire_Users_Add' => 1,
			'Bonfire_Database_Manage' => 1,
			'Bonfire_Emailer_Manage' => 1,
			'Bonfire_Logs_View' => 1,
			'Bonfire_Logs_Manage' => 1,
		),
        // Banned
		array(
			'role_id' => 3,
			'Site_Signin_Allow' => 0,
			'Site_Content_View' => 0,
			'Site_Statistics_View' => 0,
			'Site_Appearance_View' => 0,
			'Site_Settings_View' => 0,
			'Site_Developer_View' => 0,
			'Bonfire_Roles_Manage' => 0,
			'Bonfire_Users_Manage' => 0,
			'Bonfire_Users_View' => 0,
			'Bonfire_Users_Add' => 0,
			'Bonfire_Database_Manage' => 0,
			'Bonfire_Emailer_Manage' => 0,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
		),
        // User
		array(
			'role_id' => 4,
			'Site_Signin_Allow' => 1,
			'Site_Content_View' => 0,
			'Site_Statistics_View' => 0,
			'Site_Appearance_View' => 0,
			'Site_Settings_View' => 0,
			'Site_Developer_View' => 0,
			'Bonfire_Roles_Manage' => 0,
			'Bonfire_Users_Manage' => 0,
			'Bonfire_Users_View' => 0,
			'Bonfire_Users_Add' => 0,
			'Bonfire_Database_Manage' => 0,
			'Bonfire_Emailer_Manage' => 0,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
		),
	);

	/**
	 * @var array Default Roles
	 */
	private $roles_data = array(
		array(
			'role_name' => 'Administrator',
			'description' => 'Has full control over every aspect of the site.',
			'default' => 0,
			'can_delete' => 0,
		),
		array(
			'role_name' => 'Editor',
			'description' => 'Can handle day-to-day management, but does not have full power.',
			'default' => 0,
			'can_delete' => 1,
		),
		array(
			'role_name' => 'Banned',
			'description' => 'Banned users are not allowed to sign into your site.',
			'default' => 0,
			'can_delete' => 0,
		),
		array(
			'role_name' => 'User',
			'description' => 'This is the default user with access to login.',
			'default' => 1,
			'can_delete' => 0,
		),
		array(
			'role_name' => 'To Delete', /* because role_id is an auto-increment field */
			'description' => 'N/A',
			'default' => 0,
			'can_delete' => 1,
		),
		array(
			'role_name' => 'Developer',
			'description' => 'Developers typically are the only ones that can access the developer tools. Otherwise identical to Administrators, at least until the site is handed off.',
			'default' => 0,
			'can_delete' => 1,
		),
	);

	/**
	 * @var array States name/abbreviation pairs
	 */
	private $states_data = array(
		array(
			'name' => 'Alaska',
			'abbrev' => 'AK',
		),
		array(
			'name' => 'Alabama',
			'abbrev' => 'AL',
		),
		array(
			'name' => 'American Samoa',
			'abbrev' => 'AS',
		),
		array(
			'name' => 'Arizona',
			'abbrev' => 'AZ',
		),
		array(
			'name' => 'Arkansas',
			'abbrev' => 'AR',
		),
		array(
			'name' => 'California',
			'abbrev' => 'CA',
		),
		array(
			'name' => 'Colorado',
			'abbrev' => 'CO',
		),
		array(
			'name' => 'Connecticut',
			'abbrev' => 'CT',
		),
		array(
			'name' => 'Delaware',
			'abbrev' => 'DE',
		),
		array(
			'name' => 'District of Columbia',
			'abbrev' => 'DC',
		),
		array(
			'name' => 'Florida',
			'abbrev' => 'FL',
		),
		array(
			'name' => 'Georgia',
			'abbrev' => 'GA',
		),
		array(
			'name' => 'Guam',
			'abbrev' => 'GU',
		),
		array(
			'name' => 'Hawaii',
			'abbrev' => 'HI',
		),
		array(
			'name' => 'Idaho',
			'abbrev' => 'ID',
		),
		array(
			'name' => 'Illinois',
			'abbrev' => 'IL',
		),
		array(
			'name' => 'Indiana',
			'abbrev' => 'IN',
		),
		array(
			'name' => 'Iowa',
			'abbrev' => 'IA',
		),
		array(
			'name' => 'Kansas',
			'abbrev' => 'KS',
		),
		array(
			'name' => 'Kentucky',
			'abbrev' => 'KY',
		),
		array(
			'name' => 'Louisiana',
			'abbrev' => 'LA',
		),
		array(
			'name' => 'Maine',
			'abbrev' => 'ME',
		),
		array(
			'name' => 'Marshall Islands',
			'abbrev' => 'MH',
		),
		array(
			'name' => 'Maryland',
			'abbrev' => 'MD',
		),
		array(
			'name' => 'Massachusetts',
			'abbrev' => 'MA',
		),
		array(
			'name' => 'Michigan',
			'abbrev' => 'MI',
		),
		array(
			'name' => 'Minnesota',
			'abbrev' => 'MN',
		),
		array(
			'name' => 'Mississippi',
			'abbrev' => 'MS',
		),
		array(
			'name' => 'Missouri',
			'abbrev' => 'MO',
		),
		array(
			'name' => 'Montana',
			'abbrev' => 'MT',
		),
		array(
			'name' => 'Nebraska',
			'abbrev' => 'NE',
		),
		array(
			'name' => 'Nevada',
			'abbrev' => 'NV',
		),
		array(
			'name' => 'New Hampshire',
			'abbrev' => 'NH',
		),
		array(
			'name' => 'New Jersey',
			'abbrev' => 'NJ',
		),
		array(
			'name' => 'New Mexico',
			'abbrev' => 'NM',
		),
		array(
			'name' => 'New York',
			'abbrev' => 'NY',
		),
		array(
			'name' => 'North Carolina',
			'abbrev' => 'NC',
		),
		array(
			'name' => 'North Dakota',
			'abbrev' => 'ND',
		),
		array(
			'name' => 'Northern Mariana Islands',
			'abbrev' => 'MP',
		),
		array(
			'name' => 'Ohio',
			'abbrev' => 'OH',
		),
		array(
			'name' => 'Oklahoma',
			'abbrev' => 'OK',
		),
		array(
			'name' => 'Oregon',
			'abbrev' => 'OR',
		),
		array(
			'name' => 'Palau',
			'abbrev' => 'PW',
		),
		array(
			'name' => 'Pennsylvania',
			'abbrev' => 'PA',
		),
		array(
			'name' => 'Puerto Rico',
			'abbrev' => 'PR',
		),
		array(
			'name' => 'Rhode Island',
			'abbrev' => 'RI',
		),
		array(
			'name' => 'South Carolina',
			'abbrev' => 'SC',
		),
		array(
			'name' => 'South Dakota',
			'abbrev' => 'SD',
		),
		array(
			'name' => 'Tennessee',
			'abbrev' => 'TN',
		),
		array(
			'name' => 'Texas',
			'abbrev' => 'TX',
		),
		array(
			'name' => 'Utah',
			'abbrev' => 'UT',
		),
		array(
			'name' => 'Vermont',
			'abbrev' => 'VT',
		),
		array(
			'name' => 'Virgin Islands',
			'abbrev' => 'VI',
		),
		array(
			'name' => 'Virginia',
			'abbrev' => 'VA',
		),
		array(
			'name' => 'Washington',
			'abbrev' => 'WA',
		),
		array(
			'name' => 'West Virginia',
			'abbrev' => 'WV',
		),
		array(
			'name' => 'Wisconsin',
			'abbrev' => 'WI',
		),
		array(
			'name' => 'Wyoming',
			'abbrev' => 'WY',
		),
		array(
			'name' => 'Armed Forces Africa',
			'abbrev' => 'AE',
		),
		array(
			'name' => 'Armed Forces Canada',
			'abbrev' => 'AE',
		),
		array(
			'name' => 'Armed Forces Europe',
			'abbrev' => 'AE',
		),
		array(
			'name' => 'Armed Forces Middle East',
			'abbrev' => 'AE',
		),
		array(
			'name' => 'Armed Forces Pacific',
			'abbrev' => 'AP',
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
		// Email Queue
        if ( ! $this->db->tableExists($this->email_table))
        {
            $this->forge->addField($this->email_fields);
            $this->forge->addKey('id', true);
            $this->forge->createTable($this->email_table);
        }

		// Login Attempts
        if ( ! $this->db->tableExists($this->login_table))
        {
            $this->forge->addField($this->login_fields);
            $this->forge->addKey('id', true);
            $this->forge->createTable($this->login_table);
        }

		// Permissions
        if ( ! $this->db->tableExists($this->permissions_table))
        {
            $this->forge->addField($this->permission_fields);
            $this->forge->addKey('permission_id', true);
            $this->forge->addKey('role_id');
            $this->forge->createTable($this->permissions_table);

//            $this->db->insert_batch($this->permissions_table, $this->permissions_data);
/*            $prefix = $this->db->dbprefix;
            $this->db->query("INSERT INTO {$prefix}permissions VALUES(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)");
            $this->db->query("INSERT INTO {$prefix}permissions VALUES(2, 2, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
            $this->db->query("INSERT INTO {$prefix}permissions VALUES(3, 6, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)");
            $this->db->query("INSERT INTO {$prefix}permissions VALUES(4, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
            $this->db->query("INSERT INTO {$prefix}permissions VALUES(5, 4, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)");
 */
        }

		// Roles
        if ( ! $this->db->tableExists($this->roles_table))
        {
            $this->forge->addField($this->roles_fields);
            $this->forge->addKey('role_id', true);
            $this->forge->createTable($this->roles_table);

//            $this->db->insert_batch($this->roles_table, $this->roles_data);
//            $this->db->where('role_id', 5)->delete($this->roles_table);
        }

		// Sessions
        if ( ! $this->db->tableExists($this->sessions_table))
        {
            $this->forge->addField($this->sessions_fields);
            $this->forge->addKey('session_id', true);
            $this->forge->createTable($this->sessions_table);
        }

		// States
        if ( ! $this->db->tableExists($this->states_table))
        {
            $this->forge->addField($this->states_fields);
            $this->forge->addKey('id', true);
            $this->forge->createTable($this->states_table);

        //    $this->db->insert_batch($this->states_table, $this->states_data);
        }

		// Users
        if ( ! $this->db->tableExists($this->users_table))
        {
            $this->forge->addField($this->users_fields);
            $this->forge->addKey('id', true);
            $this->forge->addKey('email');
            $this->forge->createTable($this->users_table);
        }

		// User Cookies
        if ( ! $this->db->tableExists($this->cookies_table))
        {
            $this->forge->addField($this->cookies_fields);
            $this->forge->addKey('token');
            $this->forge->createTable($this->cookies_table);
        }
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$this->forge->dropTable($this->email_table);
		$this->forge->dropTable($this->login_table);
		$this->forge->dropTable($this->permissions_table);
		$this->forge->dropTable($this->roles_table);

		// Since we didn't add this table in this migration,
		// check to see if it exists before removing it
		if ($this->db->tableExists('schema_version'))
		{
			$this->forge->dropTable('schema_version');
		}

		$this->forge->dropTable($this->sessions_table);
		$this->forge->dropTable($this->states_table);
		$this->forge->dropTable($this->users_table);
		$this->forge->dropTable($this->cookies_table);
	}
}