<?php

$validation->setRules([
    'foo' => 'required|max_length[19]|even',
]);
