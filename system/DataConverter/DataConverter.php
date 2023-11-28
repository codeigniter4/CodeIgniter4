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

use CodeIgniter\DataCaster\DataCaster;

/**
 * PHP data <==> DataSource data converter
 *
 * @see \CodeIgniter\DataConverter\DataConverterTest
 */
final class DataConverter
{
    /**
     * The data caster.
     */
    private DataCaster $dataCaster;

    /**
     * @param array<string, string>       $types        [column => type]
     * @param array<string, class-string> $castHandlers Custom convert handlers
     */
    public function __construct(
        private array $types,
        array $castHandlers = []
    ) {
        $this->dataCaster = new DataCaster($castHandlers, $types);
    }

    /**
     * Converts data from DataSource to PHP array with specified type values.
     *
     * @param array<string, mixed> $data DataSource data
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
}
