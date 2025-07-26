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

namespace CodeIgniter\DataConverter;

use Closure;
use CodeIgniter\DataCaster\DataCaster;
use CodeIgniter\Entity\Entity;

/**
 * PHP data <==> DataSource data converter
 *
 * @template TEntity of object
 *
 * @see \CodeIgniter\DataConverter\DataConverterTest
 */
final class DataConverter
{
    /**
     * The data caster.
     */
    private readonly DataCaster $dataCaster;

    /**
     * @param array<string, class-string> $castHandlers Custom convert handlers
     *
     * @internal
     */
    public function __construct(
        /**
         * Type definitions.
         *
         * @var array<string, string> [column => type]
         */
        private readonly array $types,
        array $castHandlers = [],
        /**
         * Helper object.
         */
        private readonly ?object $helper = null,
        /**
         * Static reconstruct method name or closure to reconstruct an object.
         * Used by reconstruct().
         *
         * @var (Closure(array<string, mixed>): TEntity)|string|null
         */
        private readonly Closure|string|null $reconstructor = 'reconstruct',
        /**
         * Extract method name or closure to extract data from an object.
         * Used by extract().
         *
         * @var (Closure(TEntity, bool, bool): array<string, mixed>)|string|null
         */
        private readonly Closure|string|null $extractor = null,
    ) {
        $this->dataCaster = new DataCaster($castHandlers, $types, $this->helper);
    }

    /**
     * Converts data from DataSource to PHP array with specified type values.
     *
     * @param array<string, mixed> $data DataSource data
     *
     * @internal
     */
    public function fromDataSource(array $data): array
    {
        foreach (array_keys($this->types) as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->dataCaster->castAs($data[$field], $field, 'get');
            }
        }

        return $data;
    }

    /**
     * Converts PHP array to data for DataSource field types.
     *
     * @param array<string, mixed> $phpData PHP data
     *
     * @internal
     */
    public function toDataSource(array $phpData): array
    {
        foreach (array_keys($this->types) as $field) {
            if (array_key_exists($field, $phpData)) {
                $phpData[$field] = $this->dataCaster->castAs($phpData[$field], $field, 'set');
            }
        }

        return $phpData;
    }

    /**
     * Takes database data array and creates a specified type object.
     *
     * @param class-string<TEntity> $classname
     * @param array<string, mixed>  $row       Raw data from database
     *
     * @return TEntity
     *
     * @internal
     */
    public function reconstruct(string $classname, array $row): object
    {
        $phpData = $this->fromDataSource($row);

        // Use static reconstruct method.
        if (is_string($this->reconstructor) && method_exists($classname, $this->reconstructor)) {
            $method = $this->reconstructor;

            return $classname::$method($phpData);
        }

        // Use closure to reconstruct.
        if ($this->reconstructor instanceof Closure) {
            $closure = $this->reconstructor;

            return $closure($phpData);
        }

        $classObj = new $classname();

        if ($classObj instanceof Entity) {
            $classObj->injectRawData($phpData);
            $classObj->syncOriginal();

            return $classObj;
        }

        $classSet = Closure::bind(function ($key, $value): void {
            $this->{$key} = $value;
        }, $classObj, $classname);

        foreach ($phpData as $key => $value) {
            $classSet($key, $value);
        }

        return $classObj;
    }

    /**
     * Takes an object and extract properties as an array.
     *
     * @param bool $onlyChanged Only for CodeIgniter's Entity. If true, only returns
     *                          values that have changed since object creation.
     * @param bool $recursive   Only for CodeIgniter's Entity. If true, inner
     *                          entities will be cast as array as well.
     *
     * @return array<string, mixed>
     *
     * @internal
     */
    public function extract(object $object, bool $onlyChanged = false, bool $recursive = false): array
    {
        // Use extractor method.
        if (is_string($this->extractor) && method_exists($object, $this->extractor)) {
            $method = $this->extractor;
            $row    = $object->{$method}($onlyChanged, $recursive);

            return $this->toDataSource($row);
        }

        // Use closure to extract.
        if ($this->extractor instanceof Closure) {
            $closure = $this->extractor;
            $row     = $closure($object, $onlyChanged, $recursive);

            return $this->toDataSource($row);
        }

        if ($object instanceof Entity) {
            $row = $object->toRawArray($onlyChanged, $recursive);

            return $this->toDataSource($row);
        }

        $array = (array) $object;

        $row = [];

        foreach ($array as $key => $value) {
            $key = preg_replace('/\000.*\000/', '', $key);

            $row[$key] = $value;
        }

        return $this->toDataSource($row);
    }
}
