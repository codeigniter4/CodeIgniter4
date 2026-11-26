<?php

declare(strict_types=1);

use CodeIgniter\CLI\CLI;
use CodeIgniter\Debug\ConsoleExceptionRenderer;
use Config\Exceptions;

/**
 * @var Exceptions $config
 * @var Throwable  $exception
 * @var string     $message
 */
ConsoleExceptionRenderer::render($exception, $message, CLI::getOutput(), $config->sensitiveDataInTrace);
