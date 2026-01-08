<?php

use App\Libraries\Foo;

// Create an instance of the class to test
$obj = new Foo();

// or create anonymous class
// $obj = new class () extends Foo {};

// Set the value to Foo
$this->setPrivateProperty($obj, 'baz', 'oops!');

// Do normal testing...
