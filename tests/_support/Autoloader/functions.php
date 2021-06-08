<?php

if (! function_exists('autoload_foo')) {
    function autoload_foo(): string
    {
        return 'I am autoloaded by Autoloader through $files!';
    }
}

if (! defined('AUTOLOAD_CONSTANT')) {
    define('AUTOLOAD_CONSTANT', 'foo');
}
