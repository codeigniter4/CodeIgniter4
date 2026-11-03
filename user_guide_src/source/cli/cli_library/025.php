<?php

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Prompt;

// The prompt every CLI::prompt() goes through.
$prompt = CLI::getPrompt();

$color   = $prompt->ask('What is your favorite color?', ['red', 'blue']);
$hobby   = $prompt->askByKey('Pick a hobby:', ['Games', 'Sleep']);
$hobbies = $prompt->askByMultipleKeys('Pick your hobbies:', ['Games', 'Sleep', 'Music']);

// A prompt of your own, here with a validation instance of your choice.
$prompt = new Prompt(CLI::getOutput(), single_service('validation'));
$email  = $prompt->ask('What is your email?', null, 'required|valid_email');
