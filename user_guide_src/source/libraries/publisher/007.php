<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class DailyPhoto extends BaseCommand
{
    protected $group       = 'Publication';
    protected $name        = 'publish:daily';
    protected $description = 'Publishes the latest daily photo to the homepage.';

    public function run(array $params)
    {
        $publisher = new Publisher('/path/to/photos/', FCPATH . 'assets/images');

        try {
            $publisher->addPath('daily_photo.jpg')->copy(true); // `true` to enable overwrites
        } catch (Throwable $e) {
            $this->showError($e);
        }
    }
}
