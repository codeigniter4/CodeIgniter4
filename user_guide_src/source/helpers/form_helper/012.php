<?php

$data = [
    'type'  => 'hidden',
    'name'  => 'email',
    'id'    => 'hiddenemail',
    'value' => 'john@example.com',
    'class' => 'hiddenemail',
];

echo form_input($data);
/*
 * Would produce:
 * <input type="hidden" name="email" value="john@example.com" id="hiddenemail" class="hiddenemail">
 */
