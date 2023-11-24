<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\DataConverter;

use CodeIgniter\DataConverter\Cast\ArrayCast;
use CodeIgniter\DataConverter\Cast\BooleanCast;
use CodeIgniter\DataConverter\Cast\CastInterface;
use CodeIgniter\DataConverter\Cast\CSVCast;
use CodeIgniter\DataConverter\Cast\DatetimeCast;
use CodeIgniter\DataConverter\Cast\FloatCast;
use CodeIgniter\DataConverter\Cast\IntBoolCast;
use CodeIgniter\DataConverter\Cast\IntegerCast;
use CodeIgniter\DataConverter\Cast\JsonCast;
use CodeIgniter\DataConverter\Cast\TimestampCast;
use CodeIgniter\DataConverter\Cast\URICast;
use InvalidArgumentException;
use TypeError;

/**
 * PHP data <==> DataSource data converter
 *
 * @see \CodeIgniter\DataConverter\DataConverterTest
 */
class DataConverter
{
    /**
     * Custom convert handlers
     *
     * @var array<string, class-string<CastInterface>> [type => classname]
     */
    protected $castHandlers = [];

    /**
     * Default convert handlers
     *
     * @var array<string, class-string<CastInterface>> [type => classname]
     */
    private array $defaultCastHandlers = [
        'array'     => ArrayCast::class,
        'bool'      => BooleanCast::class,
        'boolean'   => BooleanCast::class,
        'csv'       => CSVCast::class,
        'datetime'  => DatetimeCast::class,
        'double'    => FloatCast::class,
        'float'     => FloatCast::class,
        'int'       => IntegerCast::class,
        'integer'   => IntegerCast::class,
        'int-bool'  => IntBoolCast::class,
        'json'      => JsonCast::class,
        'timestamp' => TimestampCast::class,
        'uri'       => URICast::class,
    ];

    /**
     * @param array $castHandlers Custom convert handlers
     */
    public function __construct(
        /**
         * @var array<string, string> [column => type]
         */
        private array $types,
        array $castHandlers = []
    ) {
        if ($castHandlers !== []) {
            $this->castHandlers = $castHandlers;
        }
    }

    /**
     * Converts data from DataSource to PHP array with specified type values.
     *
     * @param array<string, mixed> $data DataSource data
     */
    public function fromDataSource(array $data): array
    {
        $output = [];

        foreach ($data as $field => $value) {
            $output[$field] = $this->castAs($value, $field, 'fromDataSource');
        }

        return $output;
    }

    /**
     * Converts PHP array to data for DataSource field types.
     *
     * @param array<string, mixed> $phpData PHP data
     */
    public function toDataSource(array $phpData): array
    {
        $output = [];

        foreach ($phpData as $field => $value) {
            $output[$field] = $this->castAs($value, $field, 'toDataSource');
        }

        return $output;
    }

    /**
     * Provides the ability to cast an item as a specific data type.
     * Add ? at the beginning of $type  (i.e. ?string) to get `null`
     * instead of casting $value if ($value === null).
     *
     * @param mixed  $value  The value to convert
     * @param string $field  The field name
     * @param string $method Allowed to "fromDataSource" and "toDataSource"
     * @phpstan-param 'fromDataSource'|'toDataSource' $method
     */
    protected function castAs($value, string $field, string $method = 'fromDataSource'): mixed
    {
        // If the type is not defined, return as it is.
        if (! isset($this->types[$field])) {
            return $value;
        }

        $type = $this->types[$field];

        $isNullable = false;

        // Is nullable?
        if (str_starts_with($type, '?')) {
            $isNullable = true;

            if ($value === null) {
                return null;
            }

            $type = substr($type, 1);
        } elseif ($value === null) {
            $message = 'Field "' . $field . '" is not nullable, but null was passed.';

            throw new TypeError($message);
        }

        // In order not to create a separate handler for the
        // json-array type, we transform the required one.
        $type = ($type === 'json-array') ? 'json[array]' : $type;

        $params = [];

        // Attempt to retrieve additional parameters if specified
        // type[param, param2,param3]
        if (preg_match('/\A(.+)\[(.+)\]\z/', $type, $matches)) {
            $type   = $matches[1];
            $params = array_map('trim', explode(',', $matches[2]));
        }

        if ($isNullable) {
            $params[] = 'nullable';
        }

        $type = trim($type, '[]');

        $handlers = array_merge($this->defaultCastHandlers, $this->castHandlers);

        if (! isset($handlers[$type])) {
            throw new InvalidArgumentException('No such handler for "' . $field . '". Invalid type: ' . $type);
        }

        $handler = $handlers[$type];

        if (! is_subclass_of($handler, CastInterface::class)) {
            throw new InvalidArgumentException(
                'Invalid class type. It must implement CastInterface. class: ' . $handler
            );
        }

        return $handler::$method($value, $params);
    }
}
