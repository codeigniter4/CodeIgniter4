<?php

$js = [
    'id'       => 'shirts',
    'onChange' => 'some_function();',
];
echo form_dropdown('shirts', $options, 'large', $js);
