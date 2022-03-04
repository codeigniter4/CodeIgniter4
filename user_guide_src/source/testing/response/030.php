<?php

/*
 * Response body is this:
 * ['foo' => 'bar']
 */

$json = $result->getJSON();
/*
 * $json is this:
 * {
 *     "foo": "bar"
 * }
`*/
