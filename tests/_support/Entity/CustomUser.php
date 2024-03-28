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

namespace Tests\Support\Entity;

use CodeIgniter\I18n\Time;
use LogicException;

/**
 * This is a custom Entity class.
 *
 * @property string    $country
 * @property Time|null $created_at
 * @property array     $email
 * @property int       $id
 * @property string    $name
 * @property Time|null $updated_at
 */
class CustomUser
{
    private function __construct(
        private readonly int $id,
        private readonly string $name,
        private array $email,
        private readonly string $country,
        private readonly ?Time $created_at,
        private readonly ?Time $updated_at,
    ) {
    }

    public static function reconstruct(array $data): static
    {
        return new static(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['country'],
            $data['created_at'],
            $data['updated_at'],
        );
    }

    public function addEmail(string $email): void
    {
        $this->email[] = $email;
    }

    public function __isset(string $name)
    {
        if (! property_exists($this, $name)) {
            throw new LogicException('No such property: ' . $name);
        }

        return isset($this->{$name});
    }

    public function __get(string $name)
    {
        if (! property_exists($this, $name)) {
            throw new LogicException('No such property: ' . $name);
        }

        return $this->{$name};
    }
}
