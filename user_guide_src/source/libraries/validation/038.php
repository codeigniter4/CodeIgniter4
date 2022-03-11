<?php

// is_unique[table.field,ignore_field,ignore_value]

$validation->setRules([
    'name' => "is_unique[supplier.name,uuid, {$uuid}]",  // is not ok
    'name' => "is_unique[supplier.name,uuid,{$uuid} ]",  // is not ok
    'name' => "is_unique[supplier.name,uuid,{$uuid}]",   // is ok
    'name' => 'is_unique[supplier.name,uuid,{uuid}]',  // is ok - see "Validation Placeholders"
]);
