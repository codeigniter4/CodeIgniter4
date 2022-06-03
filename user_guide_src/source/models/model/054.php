<?php

namespace App\Models;

use CodeIgniter\Model;

class MyModel extends Model
{
    protected $beforeFind = ['checkCache'];

    // ...

    protected function checkCache(array $data)
    {
        // Check if the requested item is already in our cache
        if (isset($data['id']) && $item = $this->getCachedItem($data['id'])) {
            $data['data']       = $item;
            $data['returnData'] = true;

            return $data;
        }

        // ...
    }
}
