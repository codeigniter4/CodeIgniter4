<?php

namespace Math\Auth\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class AuthPublish extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:publish';
    protected $description = 'Publish Auth components into the current application.';

    public function run(array $params)
    {
        // Use the Autoloader to figure out the module path
        $source = service('autoloader')->getNamespace('Math\\Auth')[0];

        $publisher = new Publisher($source, APPPATH);

        try {
            // Add only the desired components
            $publisher->addPaths([
                'Controllers',
                'Database/Migrations',
                'Models',
            ])->merge(false); // Be careful not to overwrite anything
        } catch (Throwable $e) {
            $this->showError($e);

            return;
        }

        // If publication succeeded then update namespaces
        foreach ($publisher->getPublished() as $file) {
            // Replace the namespace
            $contents = file_get_contents($file);
            $contents = str_replace('namespace Math\\Auth', 'namespace ' . APP_NAMESPACE, $contents);
            file_put_contents($file, $contents);
        }
    }
}
