<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity;

use CodeIgniter\Entity\Cast\ArrayCast;
use CodeIgniter\Entity\Cast\BooleanCast;
use CodeIgniter\Entity\Cast\CastInterface;
use CodeIgniter\Entity\Cast\CSVCast;
use CodeIgniter\Entity\Cast\DatetimeCast;
use CodeIgniter\Entity\Cast\FloatCast;
use CodeIgniter\Entity\Cast\IntBoolCast;
use CodeIgniter\Entity\Cast\IntegerCast;
use CodeIgniter\Entity\Cast\JsonCast;
use CodeIgniter\Entity\Cast\ObjectCast;
use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\Entity\Cast\TimestampCast;
use CodeIgniter\Entity\Cast\URICast;
use CodeIgniter\Entity\Exceptions\CastException;

class DataCaster
{
    /**
     * Array of field names and the type of value to cast them as when
     * they are accessed.
     *
     * @var array<string, string>
     */
    protected array $casts = [];

    /**
     * Default convert handlers
     *
     * @var array<string, string>
     */
    private array $castHandlers = [
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
        'object'    => ObjectCast::class,
        'string'    => StringCast::class,
        'timestamp' => TimestampCast::class,
        'uri'       => URICast::class,
    ];

    public function __construct(array $castHandlers, ?array $casts = null)
    {
        $this->castHandlers = array_merge($this->castHandlers, $castHandlers);

        if ($casts !== null) {
            $this->setCasts($casts);
        }
    }

    public function setCasts(array $casts): static
    {
        $this->casts = $casts;

        return $this;
    }

    /**
     * Provides the ability to cast an item as a specific data type.
     * Add ? at the beginning of $type  (i.e. ?string) to get NULL
     * instead of casting $value if $value === null
     *
     * @param bool|float|int|string|null $value     Attribute value
     * @param string                     $attribute Attribute name
     * @param string                     $method    Allowed to "get" and "set"
     *
     * @return array|bool|float|int|object|string|null
     *
     * @throws CastException
     */
    public function castAs($value, string $attribute, string $method = 'get')
    {
        if (! isset($this->casts[$attribute])) {
            return $value;
        }

        $type = $this->casts[$attribute];

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
        $type = $type === 'json-array' ? 'json[array]' : $type;

        if (! in_array($method, ['get', 'set'], true)) {
            throw CastException::forInvalidMethod($method);
        }

        $params = [];

        // Attempt to retrieve additional parameters if specified
        // type[param, param2,param3]
        if (preg_match('/^(.+)\[(.+)\]$/', $type, $matches)) {
            $type   = $matches[1];
            $params = array_map('trim', explode(',', $matches[2]));
        }

        if ($isNullable) {
            $params[] = 'nullable';
        }

        $type = trim($type, '[]');

        $handlers = $this->castHandlers;

        if (! isset($handlers[$type])) {
            return $value;
        }

        if (! is_subclass_of($handlers[$type], CastInterface::class)) {
            throw CastException::forInvalidInterface($handlers[$type]);
        }

        return $handlers[$type]::$method($value, $params);
    }
}
