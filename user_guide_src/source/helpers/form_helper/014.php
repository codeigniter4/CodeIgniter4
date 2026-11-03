<?php

$data = [
    'name'      => 'username',
    'id'        => 'username',
    'value'     => 'johndoe',
    'maxlength' => '100',
    'size'      => '50',
    'style'     => 'width:50%',
];
echo form_input($data);
/*
 * Would produce:
 * <input type="text" name="username" value="johndoe" id="username" maxlength="100" size="50" style="width:50%">
 */
