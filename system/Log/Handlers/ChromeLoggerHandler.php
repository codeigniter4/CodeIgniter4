<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Class ChromeLoggerHandler
 *
 * Allows for logging items to the Chrome console for debugging.
 * Requires the ChromeLogger extension installed in your browser.
 *
 * @see https://craig.is/writing/chrome-logger
 * @see \CodeIgniter\Log\Handlers\ChromeLoggerHandlerTest
 */
class ChromeLoggerHandler extends BaseHandler
{
    /**
     * Version of this library - for ChromeLogger use.
     */
    public const VERSION = 1.0;

    /**
     * The number of track frames returned from the backtrace.
     *
     * @var int
     */
    protected $backtraceLevel = 0;

    /**
     * The final data that is sent to the browser.
     *
     * @var array
     */
    protected $json = [
        'version' => self::VERSION,
        'columns' => [
            'log',
            'backtrace',
            'type',
        ],
        'rows' => [],
    ];

    /**
     * The header used to pass the data.
     *
     * @var string
     */
    protected $header = 'X-ChromeLogger-Data';

    /**
     * Maps the log levels to the ChromeLogger types.
     *
     * @var array
     */
    protected $levels = [
        'emergency' => 'error',
        'alert'     => 'error',
        'critical'  => 'error',
        'error'     => 'error',
        'warning'   => 'warn',
        'notice'    => 'warn',
        'info'      => 'info',
        'debug'     => 'info',
    ];

    /**
     * Constructor
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->json['request_uri'] = current_url();
    }

    /**
     * Handles logging the message.
     * If the handler returns false, then execution of handlers
     * will stop. Any handlers that have not run, yet, will not
     * be run.
     *
     * @param string $level
     * @param string $message
     */
    public function handle($level, $message): bool
    {
        // Format our message
        $message = $this->format($message);

        // Generate Backtrace info
        $backtrace = debug_backtrace(0, $this->backtraceLevel);
        $backtrace = end($backtrace);

        $backtraceMessage = 'unknown';
        if (isset($backtrace['file'], $backtrace['line'])) {
            $backtraceMessage = $backtrace['file'] . ':' . $backtrace['line'];
        }

        // Default to 'log' type.
        $type = '';

        if (array_key_exists($level, $this->levels)) {
            $type = $this->levels[$level];
        }

        $this->json['rows'][] = [
            [$message],
            $backtraceMessage,
            $type,
        ];

        $this->sendLogs();

        return true;
    }

    /**
     * Converts the object to display nicely in the Chrome Logger UI.
     *
     * @param array|int|object|string $object
     *
     * @return array
     */
    protected function format($object)
    {
        if (! is_object($object)) {
            return $object;
        }

        // @todo Modify formatting of objects once we can view them in browser.
        $objectArray = (array) $object;

        $objectArray['___class_name'] = $object::class;

        return $objectArray;
    }

    /**
     * Attaches the header and the content to the passed in request object.
     *
     * @return void
     */
    public function sendLogs(?ResponseInterface &$response = null)
    {
        if (! $response instanceof ResponseInterface) {
            $response = service('response', null, true);
        }

        $data = base64_encode(
            mb_convert_encoding(json_encode($this->json), 'UTF-8', mb_list_encodings()),
        );

        $response->setHeader($this->header, $data);
    }
}
