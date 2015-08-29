<?php namespace Tests\Support;


class DependingClass {

    public $child;

    public function __construct( \Tests\Support\SimpleClass $simple)
    {
        $this->child = $simple;
    }

    //--------------------------------------------------------------------


}
