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

namespace CodeIgniter\Test\Interfaces;

use CodeIgniter\BaseModel;
use Faker\Generator;
use ReflectionException;

/**
 * FabricatorModel
 *
 * An interface defining the required methods and properties
 * needed for a model to qualify for use with the Fabricator class.
 * While interfaces cannot enforce properties, the following
 * are required for use with Fabricator:
 *
 * @property string $returnType
 * @property string $primaryKey
 * @property string $dateFormat
 *
 * @phpstan-import-type row_array from BaseModel
 */
interface FabricatorModel
{
    /**
     * Fetches the row of database from $this->table with a primary key
     * matching $id.
     *
     * @param int|list<int|string>|string|null $id One primary key or an array of primary keys
     *
     * @phpstan-return ($id is int|string ? row_array|object|null : list<row_array|object>)
     */
    public function find($id = null);

    /**
     * Inserts data into the current table. If an object is provided,
     * it will attempt to convert it to an array.
     *
     * @param         array|object|null     $row
     * @phpstan-param row_array|object|null $row
     * @param         bool                  $returnID Whether insert ID should be returned or not.
     *
     * @return bool|int|string
     *
     * @throws ReflectionException
     */
    public function insert($row = null, bool $returnID = true);

    /**
     * The following properties and methods are optional, but if present should
     * adhere to their definitions.
     *
     * @property array  $allowedFields
     * @property string $useSoftDeletes
     * @property string $useTimestamps
     * @property string $createdField
     * @property string $updatedField
     * @property string $deletedField
     */

    /*
     * Sets $useSoftDeletes value so that we can temporarily override
     * the softdeletes settings. Can be used for all find* methods.
     *
     * @param bool $val
     *
     * @return Model
     */
    // public function withDeleted($val = true);

    /**
     * Faked data for Fabricator.
     *
     * @param Generator $faker
     *
     * @return array|object
     */
    // public function fake(Generator &$faker);
}
