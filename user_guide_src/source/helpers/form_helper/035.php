<?php

$data = [
    'name'     => 'username',
    'id'       => 'username',
    'value'    => '',
    'required' => true,
];
echo form_input($data);
/*
 * Would produce:
 * <input type="text" name="username" value="" id="username" required>
 */
