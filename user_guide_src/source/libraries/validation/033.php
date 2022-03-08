<?php

class MyRules
{
    public function even(string $str): bool
    {
        return (int) $str % 2 === 0;
    }
}
