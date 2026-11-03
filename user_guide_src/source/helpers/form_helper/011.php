<?php

$data = [
    'name'  => 'John Doe',
    'email' => 'john@example.com',
    'url'   => 'http://example.com',
];

echo form_hidden('my_array', $data);
/*
 * Would produce:
 * <input type="hidden" name="my_array[name]" value="John Doe">
 * <input type="hidden" name="my_array[email]" value="john@example.com">
 * <input type="hidden" name="my_array[url]" value="http://example.com">
 */
