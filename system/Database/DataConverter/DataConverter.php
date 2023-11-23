<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\DataConverter;

use CodeIgniter\Database\DataConverter\Cast\ArrayCast;
use CodeIgniter\Database\DataConverter\Cast\BooleanCast;
use CodeIgniter\Database\DataConverter\Cast\CastInterface;
use CodeIgniter\Database\DataConverter\Cast\CSVCast;
use CodeIgniter\Database\DataConverter\Cast\DatetimeCast;
use CodeIgniter\Database\DataConverter\Cast\FloatCast;
use CodeIgniter\Database\DataConverter\Cast\IntBoolCast;
use CodeIgniter\Database\DataConverter\Cast\IntegerCast;
use CodeIgniter\Database\DataConverter\Cast\JsonCast;
use CodeIgniter\Database\DataConverter\Cast\TimestampCast;
use CodeIgniter\Database\DataConverter\Cast\URICast;
use InvalidArgumentException;

/**
 * PHP data <==> DB data converter
 *
 * @see \CodeIgniter\Database\DataConverter\DataConverterTest
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
     * Converts data from DB to PHP array with specified type values.
     */
    public function fromDatabase(array $dbData): array
    {
        $output = [];

        foreach ($dbData as $column => $value) {
            $output[$column] = $this->castAs($value, $column, 'fromDatabase');
        }

        return $output;
    }

    /**
     * Converts PHP array to data for DB column types.
     */
    public function toDatabase(array $phpData): array
    {
        $output = [];

        foreach ($phpData as $column => $value) {
            $output[$column] = $this->castAs($value, $column, 'toDatabase');
        }

        return $output;
    }

    /**
     * Provides the ability to cast an item as a specific data type.
     * Add ? at the beginning of $type  (i.e. ?string) to get `null`
     * instead of casting $value if ($value === null).
     *
     * @param mixed  $value  The value to convert
     * @param string $column The column name
     * @param string $method Allowed to "fromDatabase" and "toDatabase"
     * @phpstan-param 'fromDatabase'|'toDatabase' $method
     *
     * @return mixed
     */
    protected function castAs($value, string $column, string $method = 'fromDatabase')
    {
        // If the type is not defined, return as it is.
        if (! isset($this->types[$column])) {
            return $value;
        }

        $type = $this->types[$column];

        $isNullable = false;

        if (str_starts_with($type, '?')) {
            $isNullable = true;

            if ($value === null) {
                return null;
            }

            $type = substr($type, 1);
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
            throw new InvalidArgumentException('No such handler for "' . $column . '". Invalid type: ' . $type);
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
