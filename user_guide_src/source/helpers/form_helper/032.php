<?php

$data = [
    'name'    => 'button',
    'id'      => 'button',
    'value'   => 'true',
    'type'    => 'reset',
    'content' => 'Reset',
];

echo form_button($data);
// Would produce: <button name="button" id="button" value="true" type="reset">Reset</button>
