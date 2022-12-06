<?php

// When the request body is 'foo=one&bar=two&baz[]=10&baz[]=20'
var_dump($request->getRawInputVar('bar'));
// Outputs: two

// foo=one&bar=two&baz[]=10&baz[]=20
var_dump($request->getRawInputVar(['foo', 'bar']));
/*
 * Outputs:
 * [
 *      'foo' => 'one',
 *      'bar' => 'two'
 * ]
 */

// foo=one&bar=two&baz[]=10&baz[]=20
var_dump($request->getRawInputVar('baz'));
/*
 * Outputs:
 * [
 *      '10',
 *      '20'
 * ]
 */

// foo=one&bar=two&baz[]=10&baz[]=20
var_dump($request->getRawInputVar('baz.0'));
// Outputs: 10
