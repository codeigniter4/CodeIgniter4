<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Test\Constraints;

use CodeIgniter\Database\ConnectionInterface;
use PHPUnit\Framework\Constraint\Constraint;

class SeeInDatabase extends Constraint
{
    /**
     * The number of results that will show in the database
     * in case of failure.
     *
     * @var int
     */
    protected $show = 3;

    /**
     * @var ConnectionInterface
     */
    protected $db;

    /**
     * Data used to compare results against.
     *
     * @var array
     */
    protected $data;

    /**
     * SeeInDatabase constructor.
     */
    public function __construct(ConnectionInterface $db, array $data)
    {
        $this->db   = $db;
        $this->data = $data;
    }

    /**
     * Check if data is found in the table
     *
     * @param mixed $table
     */
    protected function matches($table): bool
    {
        return $this->db->table($table)->where($this->data)->countAllResults() > 0;
    }

    /**
     * Get the description of the failure
     *
     * @param mixed $table
     */
    protected function failureDescription($table): string
    {
        return sprintf(
            "a row in the table [%s] matches the attributes \n%s\n\n%s",
            $table,
            $this->toString(JSON_PRETTY_PRINT),
            $this->getAdditionalInfo($table)
        );
    }

    /**
     * Gets additional records similar to $data.
     */
    protected function getAdditionalInfo(string $table): string
    {
        $builder = $this->db->table($table);

        $similar = $builder->where(
            array_key_first($this->data),
            $this->data[array_key_first($this->data)]
        )->limit($this->show)->get()->getResultArray();

        if ($similar !== []) {
            $description = 'Found similar results: ' . json_encode($similar, JSON_PRETTY_PRINT);
        } else {
            // Does the table have any results at all?
            $results = $this->db->table($table)
                ->limit($this->show)
                ->get()
                ->getResultArray();

            if ($results !== []) {
                return 'The table is empty.';
            }

            $description = 'Found: ' . json_encode($results, JSON_PRETTY_PRINT);
        }

        $total = $this->db->table($table)->countAll();
        if ($total > $this->show) {
            $description .= sprintf(' and %s others', $total - $this->show);
        }

        return $description;
    }

    /**
     * Gets a string representation of the constraint
     *
     * @param int $options
     */
    public function toString($options = 0): string
    {
        return json_encode($this->data, $options);
    }
}
