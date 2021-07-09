<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Database;

use CodeIgniter\CLI\CLI;
use Config\Database;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;

/**
 * Class Seeder
 */
class Seeder
{
    /**
     * The name of the database group to use.
     *
     * @var string
     */
    protected $DBGroup;

    /**
     * Where we can find the Seed files.
     *
     * @var string
     */
    protected $seedPath;

    /**
     * An instance of the main Database configuration
     *
     * @var Database
     */
    protected $config;

    /**
     * Database Connection instance
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * Database Forge instance.
     *
     * @var Forge
     */
    protected $forge;

    /**
     * If true, will not display CLI messages.
     *
     * @var bool
     */
    protected $silent = false;

    /**
     * Faker Generator instance.
     *
     * @var Generator|null
     */
    private static $faker;

    /**
     * Seeder constructor.
     *
     * @param Database            $config
     * @param BaseConnection|null $db
     */
    public function __construct(Database $config, ?BaseConnection $db = null)
    {
        $this->seedPath = $config->filesPath ?? APPPATH . 'Database/';

        if (empty($this->seedPath)) {
            throw new InvalidArgumentException('Invalid filesPath set in the Config\Database.');
        }

        $this->seedPath = rtrim($this->seedPath, '\\/') . '/Seeds/';

        if (! is_dir($this->seedPath)) {
            throw new InvalidArgumentException('Unable to locate the seeds directory. Please check Config\Database::filesPath');
        }

        $this->config = &$config;

        $db = $db ?? Database::connect($this->DBGroup);

        $this->db    = &$db;
        $this->forge = Database::forge($this->DBGroup);
    }

    /**
     * Gets the Faker Generator instance.
     *
     * @return Generator|null
     */
    public static function faker(): ?Generator
    {
        if (self::$faker === null && class_exists(Factory::class)) {
            self::$faker = Factory::create();
        }

        return self::$faker;
    }

    /**
     * Loads the specified seeder and runs it.
     *
     * @param string $class
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function call(string $class)
    {
        $class = trim($class);

        if ($class === '') {
            throw new InvalidArgumentException('No seeder was specified.');
        }

        if (strpos($class, '\\') === false) {
            $path = $this->seedPath . str_replace('.php', '', $class) . '.php';

            if (! is_file($path)) {
                throw new InvalidArgumentException('The specified seeder is not a valid file: ' . $path);
            }

            // Assume the class has the correct namespace
            // @codeCoverageIgnoreStart
            $class = APP_NAMESPACE . '\Database\Seeds\\' . $class;

            if (! class_exists($class, false)) {
                require_once $path;
            }
            // @codeCoverageIgnoreEnd
        }

        /**
         * @var Seeder
         */
        $seeder = new $class($this->config);
        $seeder->setSilent($this->silent)->run();

        unset($seeder);

        if (is_cli() && ! $this->silent) {
            CLI::write("Seeded: {$class}", 'green');
        }
    }

    /**
     * Sets the location of the directory that seed files can be located in.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->seedPath = rtrim($path, '\\/') . '/';

        return $this;
    }

    /**
     * Sets the silent treatment.
     *
     * @param bool $silent
     *
     * @return $this
     */
    public function setSilent(bool $silent)
    {
        $this->silent = $silent;

        return $this;
    }

    /**
     * Run the database seeds. This is where the magic happens.
     *
     * Child classes must implement this method and take care
     * of inserting their data here.
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function run()
    {
    }
}
