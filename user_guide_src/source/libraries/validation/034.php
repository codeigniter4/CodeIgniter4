<?php

class MyRules
{
    public function even($value): bool
    {
        return (int) $value % 2 === 0;
    }
}
