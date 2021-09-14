<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Session Handler
     * --------------------------------------------------------------------------
     *
     * The session storage handler to use:
     * - `CodeIgniter\Session\Handlers\DatabaseHandler`
     * - `CodeIgniter\Session\Handlers\FileHandler`
     * - `CodeIgniter\Session\Handlers\MemcachedHandler`
     * - `CodeIgniter\Session\Handlers\RedisHandler`
     *
     * @var string
     */
    public $handler = FileHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Name
     * --------------------------------------------------------------------------
     *
     * The name of the session which is used as cookie name.
     * It should only contain alphanumeric characters.
     *
     * @var string
     */
    public $name = 'ci_session';

    /**
     * --------------------------------------------------------------------------
     * Session Life Time
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     *
     * @var int
     */
    public $lifetime = 7200;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to and is driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' driver, it's a table name.
     * Please read up the manual for the format with other session drivers.
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     *
     * @var string
     */
    public $savePath = WRITEPATH . 'session';

    /**
     * --------------------------------------------------------------------------
     * Session Match IP
     * --------------------------------------------------------------------------
     *
     * Whether to match the user's IP address when reading the session data.
     *
     * NOTE: If you're using the database driver, don't forget to update
     *       your session table's PRIMARY KEY when changing this setting.
     *
     * @var bool
     */
    public $matchIP = false;

    /**
     * --------------------------------------------------------------------------
     * Session Time to Live
     * --------------------------------------------------------------------------
     *
     * How many seconds between CI regenerating the session ID.
     *
     * @var int
     */
    public $ttl = 300;

    /**
     * --------------------------------------------------------------------------
     * Session Regenerate
     * --------------------------------------------------------------------------
     *
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     *
     * @var bool
     */
    public $regenerate = false;
}
