<?php

use CodeIgniter\Database\Exceptions\DataException;

try {
    $user = $userModel->find($id);
} catch (DataException $e) {
    // do something here...

    throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
}
