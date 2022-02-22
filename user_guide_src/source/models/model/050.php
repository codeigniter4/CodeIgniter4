<?php

protected function hashPassword(array $data)
{
    if (! isset($data['data']['password'])) {
        return $data;
    }

    $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
    unset($data['data']['password']);

    return $data;
}
