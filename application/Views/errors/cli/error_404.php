<?php 
declare(strict_types=1);

defined('BASEPATH') OR exit('No direct script access allowed');

use CodeIgniter\CLI\CLI;

CLI::error('ERROR: '.$heading);
CLI::write($message);
CLI::newLine();
