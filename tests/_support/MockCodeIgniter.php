<?php namespace CodeIgniter;

class MockCodeIgniter extends CodeIgniter
{
    protected function callExit($code)
    {
        // Do not call exit() in testing.
    }
}
