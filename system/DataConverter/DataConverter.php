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
     * @param array<string, string> $types        [column => type]
     * @param array                 $castHandlers Custom convert handlers
     */
    public function __construct(array $types, array $castHandlers = [])
    {
        $this->dataCaster = new DataCaster($castHandlers, $types);
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
            $output[$field] = $this->dataCaster->castAs($value, $field, 'get');
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
            $output[$field] = $this->dataCaster->castAs($value, $field, 'set');
        }

        return $output;
    }
}
