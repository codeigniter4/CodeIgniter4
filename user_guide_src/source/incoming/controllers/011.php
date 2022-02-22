<?php

public function _remap($method)
{
    if ($method === 'some_method') {
        return $this->$method();
    } else {
        return $this->default_method();
    }
}
