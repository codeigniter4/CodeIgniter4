<?php

namespace Tests\Support\Log;

/**
 * A class used to test object formatting
 */
class Foo
{

    public $foo = 1;
    protected $bar = 2;
    private $baz = 'Hello World!';

    public function getBaz()
    {
	return $this->baz;
    }

}
