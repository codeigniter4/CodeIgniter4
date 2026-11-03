<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Output;
use CodeIgniter\CLI\ProgressBar;
use CodeIgniter\CLI\Table;
use CodeIgniter\Test\Mock\MockInputOutput;

// The output every CLI::write() goes to.
$output = CLI::getOutput();

$output->write('Hello', 'green');
$output->error('Something went wrong.');

// Tables and progress bars are built on the output.
$output->write((new Table([[1, 'bar']], ['ID', 'Title']))->render());

$bar = new ProgressBar($output);
$bar->advanceTo(5, 10);
$bar->advanceTo(10, 10);

// An output of your own, here capturing everything written to it.
$captured = new Output($io = new MockInputOutput());
$captured->write('captured');

echo $io->getOutput();
