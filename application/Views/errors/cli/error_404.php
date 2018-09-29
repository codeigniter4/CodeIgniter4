<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use CodeIgniter\CLI\CLI;

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
