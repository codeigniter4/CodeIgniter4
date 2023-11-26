<?php

$data = [
    'name'  => 'John Doe',
    'email' => 'john@example.com',
    'url'   => 'http://example.com',
];

echo form_hidden($data);
/*
 * Would produce:
 * <input type="hidden" name="name" value="John Doe">
 * <input type="hidden" name="email" value="john@example.com">
 * <input type="hidden" name="url" value="http://example.com">
 */
