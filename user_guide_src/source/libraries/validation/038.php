<?php

// is_unique[table.field,ignore_field,ignore_value]

$validation->setRules([
    'name' => "max_length[36]|is_unique[supplier.name,uuid, {$uuid}]", // is not ok
    'name' => "max_length[36]|is_unique[supplier.name,uuid,{$uuid} ]", // is not ok
    'name' => "max_length[36]|is_unique[supplier.name,uuid,{$uuid}]",  // is ok
    'name' => 'max_length[36]|is_unique[supplier.name,uuid,{uuid}]',   // is ok - see "Validation Placeholders"
]);
// Warning: If `$uuid` is a user input, be sure to validate the format of the value before using it.
// Otherwise, it is vulnerable.
