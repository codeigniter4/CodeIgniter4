<?php

/*
 * With a request body of:
 * {
 *     "foo": "bar",
 *     "fizz": {
 *         "buzz": "baz"
 *     }
 * }
 */

$data = $request->getJsonVar('foo');
// $data = "bar"

$data = $request->getJsonVar('fizz.buzz');
// $data = "baz"
