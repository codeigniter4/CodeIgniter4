<?php

use CodeIgniter\CLI\CLI;
use Config\App;

return [
    'foo' => 'The command will use this as foo.',
    'bar' => 'The command will use this as bar.',
    'baz' => 'The baz is here.',
    'bas' => CLI::color('bas', 'green') . (new App())->baseURL,
];
