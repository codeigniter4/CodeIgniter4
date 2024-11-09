<?php

namespace App\Entity;

use App\ValueObject\Name;
use DomainException;

class User
{
    public Name $name;
    public string $createdAt;

    public function __construct(array $data = [])
    {
        if (! service('validation')
            ->setRules([
                'name'       => 'required|valid_name',
                'created_at' => 'valid_date[d/m/Y]',
            ])
            ->run($data)
        ) {
            // The validation failed
            throw new DomainException('Invalid data for "User"');
        }

        // Working with allowed data
        $this->name      = $data['name'];
        $this->createdAt = $data['created_at'];
    }
}
