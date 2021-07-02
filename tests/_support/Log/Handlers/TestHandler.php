<?php

namespace Tests\Support\Log\Handlers;

/**
 * Class TestHandler
 *
 * A simple LogHandler that stores the logs in memory.
 * Only used for testing purposes.
 */
class TestHandler extends \CodeIgniter\Log\Handlers\FileHandler
{
    /**
     * Local storage for logs.
     *
     * @var array
     */
    protected static $logs = [];

    /**
     * Where would the log be written?
     */
    //--------------------------------------------------------------------

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->handles     = $config['handles'] ?? [];
        $this->destination = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;

        self::$logs = [];
    }

    //--------------------------------------------------------------------

    /**
     * Handles logging the message.
     * If the handler returns false, then execution of handlers
     * will stop. Any handlers that have not run, yet, will not
     * be run.
     *
     * @param $level
     * @param $message
     *
     * @return bool
     */
    public function handle($level, $message): bool
    {
        $date = date($this->dateFormat);

        self::$logs[] = strtoupper($level) . ' - ' . $date . ' --> ' . $message;

        return true;
    }

    //--------------------------------------------------------------------

    public static function getLogs()
    {
        return self::$logs;
    }

    //--------------------------------------------------------------------
}
