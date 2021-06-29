<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Format;

use CodeIgniter\Format\Exceptions\FormatException;
use Config\Format;
use function in_array;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use const JSON_ERROR_NONE;
use const JSON_ERROR_RECURSION;
use const JSON_PARTIAL_OUTPUT_ON_ERROR;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * JSON data formatter
 */
class JSONFormatter implements FormatterInterface
{
    /**
     * Takes the given data and formats it.
     *
     * @param mixed $data
     *
     * @return bool|string (JSON string | false)
     */
    public function format($data)
    {
        $config = new Format();

        $options = $config->formatterOptions['application/json'] ?? JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $options = $options | JSON_PARTIAL_OUTPUT_ON_ERROR;

        $options = ENVIRONMENT === 'production' ? $options : $options | JSON_PRETTY_PRINT;

        $result = json_encode($data, $options, 512);

        if (! in_array(json_last_error(), [JSON_ERROR_NONE, JSON_ERROR_RECURSION], true)) {
            throw FormatException::forInvalidJSON(json_last_error_msg());
        }

        return $result;
    }
}
