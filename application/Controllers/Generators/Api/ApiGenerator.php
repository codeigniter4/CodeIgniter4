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

use Myth\CLI as CLI;

/**
 * Class ApiGenerator
 *
 * Asks the user a series of questions and creates any needed files based
 * upon the authentication type.
 *
 * Auth Type |  Files Created
 * ----------+----------------------------------
 * basic        none
 * digest       migration, modifies adds event to config
 * keys         migration, modifies adds event to config
 */
class ApiGenerator extends \Myth\Forge\BaseGenerator {

	protected $auth_type;

	protected $destination;

	//--------------------------------------------------------------------

	/**
	 * @param array $segments
	 * @param bool $quiet
	 */
	public function run($segments=[], $quiet=false)
	{
		// Show an index?
		if (empty($segments[0]))
		{
			return $this->displayIndex();
		}

		$action = array_shift($segments);

		switch ($action)
		{
			case 'install':
				$this->install();
				break;
			case 'scaffold':
				$this->scaffold($segments, $quiet);
				break;
			default:
				if (! $quiet)
				{
					CLI::write('Nothing to do.', 'green');
				}
				break;
		}
	}

	//--------------------------------------------------------------------

	private function displayIndex()
	{
		CLI::write("\nAvailable API Generators");

		CLI::write( CLI::color('install', 'yellow') .'   install        Creates migrations, alters config file for desired authentication' );
		CLI::write( CLI::color('scaffold', 'yellow') .'  scaffold <name>  Creates basic CRUD controller, routes, and optionally API Blueprint files' );
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Scaffold Methods
	//--------------------------------------------------------------------

	/**
	 * Quickly creates boilerplate code for a new API resource.
	 *
	 * @param $segments
	 * @param $quiet
	 */
	private function scaffold( $segments, $quiet )
	{
		$this->load->helper('inflector');

		/*
		 * Gather needed info
		 */

		// Resource name
		list($resource, $version, $blueprint, $model) = array_pad($segments, 4, null);

		if (empty($resource))
		{
			$resource = strtolower( CLI::prompt('Resource name') );
		}

		// Resources should be plural, but we'll need a singular name also.
		$resource_single = singular($resource);
		$resource_plural = plural($resource);

		// API version
		if (empty($version))
		{
			$version = $this->askVersion();
		}

		if (empty($blueprint))
		{
			$blueprint = $this->askBlueprint();
		}

		if (empty($model))
		{
			$model = $this->detectModel( $resource_single );
		}

		/*
		 * Start Building
		 */
		$this->destination = APPPATH .'controllers/';
		if (! empty($version))
		{
			$this->destination .= $version .'/';
		}

		// Controller
		if (! $this->createController($resource_single, $resource_plural, $version) )
		{
			CLI::error('Unknown error creating Controller.');
			exit(1);
		}

		// Language Files
		if (! $this->createLanguage($resource_single, $resource_plural, $version, $model) )
		{
			CLI::error('Unknown error creating Language File.');
			exit(1);
		}
//
		// Blueprint File
		if ($blueprint)
		{
			if (! $this->createBlueprint($resource_single, $resource_plural, $version, $model) )
			{
				CLI::error('Unknown error creating Blueprint file.');
				exit(1);
			}
		}

		// Modify Routes
		if (! $this->addRoutes($resource_plural, $version) )
		{
			CLI::error('Unknown error adding Routes.');
			exit(1);
		}

	}

	//--------------------------------------------------------------------

	/**
	 * Gets the version number/folder name for the controller to live.
	 *
	 * @return string
	 */
	private function askVersion()
	{
		CLI::write("\nVersions are simply controller folders (i.e. controllers/v1/...)");
		CLI::write("(Enter 'na' for no version folder)");

		$version = strtolower( CLI::prompt('Version name', 'v1') );
		$version = $version == 'na' ? '' : $version;

		return $version;
	}

	//--------------------------------------------------------------------

	/**
	 * Should we create an API Blueprint file for this controller?
	 *
	 * @return bool
	 */
	private function askBlueprint()
	{
		CLI::write("\nAPI Blueprint is a plain-text API documentation starter.");
		CLI::write("See: ". CLI::color('https://apiblueprint.org', 'light_blue'));

		$make_blueprint = CLI::prompt('Create Blueprint file?', ['y', 'n']);

		return $make_blueprint == 'y' ? true : false;
	}

	//--------------------------------------------------------------------

	private function detectModel($name)
	{
		$model_name = ucfirst($name) .'_model.php';

		if (! file_exists(APPPATH .'models/'. $model_name))
		{
			CLI::write("\nUnable to find model named: {$model_name}");
			$model_name = CLI::prompt('Model filename');
		}
		else
		{
			CLI::write("Using model: ". CLI::color($model_name, 'yellow') );
		}

		return $model_name;
	}

	//--------------------------------------------------------------------

    /**
     * Creates the new APIController - based resource with basic CRUD.
     *
     * @param $single
     * @param $plural
     * @param $version
     *
     * @return $this
     * @internal param $name
     *
     */
	private function createController( $single, $plural, $version )
	{
		$data = [
			'today'             => date( 'Y-m-d H:ia' ),
			'model_name'        => strtolower($single) .'_model',
			'plural'            => $plural,
			'single'            => $single,
			'class_name'        => ucfirst($plural),
			'version'           => $version
		];

		$destination = $this->destination . ucfirst($plural) .'.php';

		return $this->copyTemplate( 'controller', $destination, $data, $this->overwrite );
	}

	//--------------------------------------------------------------------

    /**
     * Creates the language file to accompany the controller.
     *
     * @param $single
     * @param $plural
     * @param $version
     *
     * @return $this
     */
	private function createLanguage( $single, $plural, $version )
	{
        $data = [
            'plural'            => $plural,
            'single'            => $single,
            'uc_single'         => ucfirst($single),
            'uc_plural'         => ucfirst($plural),
        ];

        $destination = APPPATH ."language/english/api_{$plural}_lang.php";

        return $this->copyTemplate( 'lang', $destination, $data, $this->overwrite );
	}

	//--------------------------------------------------------------------

    /**
     * Creates the API Blueprint file for that resource in
     * APPPATH/docs/api
     *
     * @param $single
     * @param $plural
     * @param $version
     * @param $model
     *
     * @return $this
     * @internal param $name
     *
     */
	private function createBlueprint( $single, $plural, $version, $model )
	{
		$version = rtrim($version, '/');
		if (! empty($version))
		{
			$version .= '/';
		}

		// Load the model so we can use the correct table to use
        $model = strtolower( str_replace('.php', '', $model) );
		$this->load->model( $model, $model, true );

        $obj = $this->formatObject($model);


		$data = [
			'plural'            => $plural,
			'single'            => $single,
			'uc_single'         => ucfirst($single),
			'uc_plural'         => ucfirst($plural),
			'version'           => $version,
			'site_url'          => site_url(),
            'formatted'         => $obj
		];

		$destination = APPPATH .'docs/api/'. $version . $plural .'.md';

		$success = $this->copyTemplate( 'blueprint', $destination, $data, $this->overwrite );

        if (! $this->updateTOC($plural, $version))
        {
            CLI::write("\tUnable to modify the toc file.", 'light_red');
        }

        return $success;
	}

	//--------------------------------------------------------------------

    /**
     * Modifies the _toc.ini file (or creates) for the specified Blueprint docs.
     *
     * @param $plural
     * @param $version
     *
     * @return $this|bool
     */
    private function updateTOC( $plural, $version )
    {
        $path = APPPATH .'docs/_toc.ini';

        // We need a TOC file to exist if we're going to modify it silly.
        if (! file_exists($path))
        {
            if (! $this->copyTemplate('toc', $path))
            {
                return false;
            }
        }

        $version = rtrim($version, '/ ');
        if (! empty($version))
        {
            $version .= '/';
        }

        $ucname = ucfirst($plural);

        $content = "api/{$version}{$plural}\t= {$ucname}\n";

        return $this->injectIntoFile($path, $content);
    }

    //--------------------------------------------------------------------

    /**
     * Creates a generic representation of the object from the database
     * table.
     *
     * @param $model
     *
     * @return string
     */
    private function formatObject( $model )
    {
        $fields = $this->db->field_data($this->$model->table_name);

        $obj = '';

        foreach ($fields as $field)
        {
            $obj .= "\"$field->name\":  ";

            switch ($field->type)
            {
                case 'tinyint':
                    $obj .= "0,\n";
                    break;
                case 'int':
                case 'bigint':
                    $obj .= "1234,\n";
                    break;
                case 'float':
                case 'double':
                    $obj .= "123.45,\n";
                    break;
                case 'date':
                    $obj .= date("\"Y-m-d\",\n");
                    break;
                case 'datetime':
                    $obj .= date("\"Y-m-d H:i:s\",\n");
                    break;
            }

            if ($field->name == 'email')
            {
                $obj .= "\"someone@example.com\",\n";
            }
            else if (strpos('name', $field->name) !== false)
            {
                $obj .= "\"Lefty\",\n";
            }
            else if (in_array($field->type, ['char', 'varchar', 'text']))
            {
                $obj .= "\"Some default string\",\n";
            }
        }

        return $obj;
    }

    //--------------------------------------------------------------------

    /**
     * Modifies the routes file to include a line for the API endpoints
     * for this resource.
     *
     * @param string $plural
     * @param string $version
     *
     * @return $this
     */
	private function addRoutes( $plural, $version )
	{
        $path = APPPATH .'config/routes.php';

        $version = rtrim($version, ', ');
        if (! empty($version))
        {
            $version .='/';
        }

        $content = "\$routes->resources('{$version}{$plural}');\n";

        return $this->injectIntoFile($path, $content, ['after' => "// Auto-generated routes go here\n"]);
	}

	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Installer Methods
	//--------------------------------------------------------------------

	/**
	 * Handles actually installing the tables and correct authentication
	 * type.
	 */
	private function install( )
	{
		CLI::write("Available Auth Types: ". CLI::color('basic, digest, none', 'yellow') );

		$this->auth_type = trim( CLI::prompt('Auth type') );

		switch ($this->auth_type)
		{
			case 'basic':
				$this->setupBasic();
				$this->readme('readme_basic.txt');
				break;
			case 'digest':
				$this->setupDigest();
				$this->readme('readme_digest.txt');
				break;
		}

		$this->setupLogging();
	}

	//--------------------------------------------------------------------


	private function setupBasic()
	{
		$this->setAuthType('basic');
	}

	//--------------------------------------------------------------------

	private function setupDigest()
	{
		$this->makeMigration('migration', 'Add_digest_key_to_users');

		$this->setAuthType('digest');
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------

	private function setupLogging()
	{
		$shouldWe = CLI::prompt('Enable Request Logging?', ['y', 'n']);

		if (strtolower($shouldWe) != 'y')
		{
			return;
		}

		$this->makeMigration('log_migration', 'Create_api_log_table');

		// Update the config setting
		$content = "config['api.enable_logging']    = true;";
		$this->injectIntoFile(APPPATH .'config/api.php', $content, ['regex' => "/config\['api.enable_logging']\s+=\s+[a-zA-Z]+;/u"] );
	}

	//--------------------------------------------------------------------


	private function makeMigration( $tpl, $name )
	{
		// Create the migration
		$this->load->library('migration');

		$destination = $this->migration->determine_migration_path('app', true);

		$file = $this->migration->make_name( $name );

		$destination = rtrim($destination, '/') .'/'. $file;

		if (! $this->copyTemplate( $tpl, $destination, [], true) )
		{
			CLI::error('Error creating migration file.');
		}

		$this->setAuthType('digest');
	}

	//--------------------------------------------------------------------

	/**
	 * Modifies the config/api.php file and sets the Auth type
	 *
	 * @param $type
	 */
	private function setAuthType( $type )
	{
		$content = "config['api.auth_type']    = '{$type}';";

		$this->injectIntoFile(APPPATH .'config/api.php', $content, ['regex' => "/config\['api.auth_type']\s+=\s+'[a-zA-Z]+';/u"] );
	}

	//--------------------------------------------------------------------

}
