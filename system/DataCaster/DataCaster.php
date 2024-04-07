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

namespace CodeIgniter\DataCaster;

use CodeIgniter\DataCaster\Cast\ArrayCast;
use CodeIgniter\DataCaster\Cast\BooleanCast;
use CodeIgniter\DataCaster\Cast\CastInterface;
use CodeIgniter\DataCaster\Cast\CSVCast;
use CodeIgniter\DataCaster\Cast\DatetimeCast;
use CodeIgniter\DataCaster\Cast\FloatCast;
use CodeIgniter\DataCaster\Cast\IntBoolCast;
use CodeIgniter\DataCaster\Cast\IntegerCast;
use CodeIgniter\DataCaster\Cast\JsonCast;
use CodeIgniter\DataCaster\Cast\TimestampCast;
use CodeIgniter\DataCaster\Cast\URICast;
use CodeIgniter\Entity\Cast\CastInterface as EntityCastInterface;
use CodeIgniter\Entity\Exceptions\CastException;
use InvalidArgumentException;

final class DataCaster
{
    /**
     * Array of field names and the type of value to cast.
     *
     * @var array<string, string> [field => type]
     */
    private array $types = [];

    /**
     * Convert handlers
     *
     * @var array<string, class-string> [type => classname]
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
        'timestamp' => TimestampCast::class,
        'uri'       => URICast::class,
    ];

    /**
     * @param array<string, class-string>|null $castHandlers Custom convert handlers
     * @param array<string, string>|null       $types        [field => type]
     * @param object|null                      $helper       Helper object.
     * @param bool                             $strict       Strict mode? Set to false for casts for Entity.
     */
    public function __construct(
        ?array $castHandlers = null,
        ?array $types = null,
        private readonly ?object $helper = null,
        private readonly bool $strict = true
    ) {
        $this->castHandlers = array_merge($this->castHandlers, $castHandlers);

        if ($types !== null) {
            $this->setTypes($types);
        }

        if ($this->strict) {
            foreach ($this->castHandlers as $handler) {
                if (
                    ! is_subclass_of($handler, CastInterface::class)
                    && ! is_subclass_of($handler, EntityCastInterface::class)
                ) {
                    throw new InvalidArgumentException(
                        'Invalid class type. It must implement CastInterface. class: ' . $handler
                    );
                }
            }
        }
    }

    /**
     * This method is only for Entity.
     *
     * @TODO if Entity::$casts is readonly, we don't need this method.
     *
     * @param array<string, string> $types [field => type]
     *
     * @return $this
     *
     * @internal
     */
    public function setTypes(array $types): static
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Provides the ability to cast an item as a specific data type.
     * Add ? at the beginning of the type (i.e. ?string) to get `null`
     * instead of casting $value when $value is null.
     *
     * @param         mixed       $value  The value to convert
     * @param         string      $field  The field name
     * @param         string      $method Allowed to "get" and "set"
     * @phpstan-param 'get'|'set' $method
     */
    public function castAs(mixed $value, string $field, string $method = 'get'): mixed
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
            if ($this->strict) {
                $message = 'Field "' . $field . '" is not nullable, but null was passed.';

                throw new InvalidArgumentException($message);
            }
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

        $handlers = $this->castHandlers;

        if (! isset($handlers[$type])) {
            throw new InvalidArgumentException(
                'No such handler for "' . $field . '". Invalid type: ' . $type
            );
        }

        $handler = $handlers[$type];

        if (
            ! $this->strict
            && ! is_subclass_of($handler, CastInterface::class)
            && ! is_subclass_of($handler, EntityCastInterface::class)
        ) {
            throw CastException::forInvalidInterface($handler);
        }

        return $handler::$method($value, $params, $this->helper);
    }
}
