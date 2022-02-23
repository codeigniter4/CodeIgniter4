<?php

public function product(int $id)
{
    $data = [
        'id'   => $id,
        'name' => $this->request->getVar('name'),
    ];
    $rule = [
        'id'   => 'integer',
        'name' => 'required|max_length[255]',
    ];

    if (! $this->validateData($data, $rule) {
        // ...
    }

    // ...
}