<?php

// using the same data from above
$flattened = array_flatten_with_dots($arrayToFlatten, 'foo_');
/*
 * $flattened is now:
 * [
 *     'foo_personal.first_name' => 'john',
 *     'foo_personal.last_name'  => 'smith',
 *     'foo_personal.age'        => '26',
 *     'foo_personal.address'    => 'US',
 *     'foo_other_details'       => 'marines officer',
 * ]
 */
